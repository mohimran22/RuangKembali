<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\TransactionApprovedNotification;
use App\Notifications\TransactionRejectedNotification;
use App\Models\Transaction;
use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalDetail;
use App\Helpers\ActiveRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    private function authorizeStaff(): void
{
    abort_unless(in_array(ActiveRole::name(), ['Super-Admin', 'Tim']), 403);
}

public function index(Request $request)
{
    $this->authorizeStaff();
    $status = $request->get('status');

    $query = Transaction::with([
        'event',
        'registeredBy',
        'registrations.user:id,fullname',
    ])
    ->withCount('registrations')
    ->when($status, function ($query) use ($status) {
        $query->where('status', $status);
    })
    ->latest();

    if ($request->ajax()) {

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('event_name', function ($transaction) {
                return e($transaction->event->name ?? '-');
            })

            ->addColumn('registered_by_name', function ($transaction) {
                return e(
                    $transaction->registeredBy->fullname
                    ?? $transaction->registeredBy->name
                    ?? '-'
                );
            })

            ->addColumn('participants', function ($transaction) {

                if ($transaction->registrations_count <= 0) {
                    return '0';
                }

                $names = $transaction->registrations
                    ->map(function ($registration) {
                        return $registration->participant_name ?? '-';
                    })
                    ->filter()
                    ->implode(', ');

                return $transaction->registrations_count
                    . ' <i class="ti ti-info-circle text-secondary ms-1"
                            data-bs-toggle="tooltip"
                            title="' . e($names) . '"></i>';
            })

            ->editColumn('total_amount', function ($transaction) {
                return 'Rp ' . number_format(
                    $transaction->total_amount,
                    0,
                    ',',
                    '.'
                );
            })

            ->addColumn('status_badge', function ($transaction) {

                $statusMap = [
                    'pending' => [
                        'label' => 'Menunggu Pembayaran',
                        'class' => 'bg-warning-lt',
                    ],

                    'waiting_confirmation' => [
                        'label' => 'Menunggu Konfirmasi',
                        'class' => 'bg-blue-lt',
                    ],

                    'paid' => [
                        'label' => 'Lunas',
                        'class' => 'bg-success-lt',
                    ],

                    'rejected' => [
                        'label' => 'Ditolak',
                        'class' => 'bg-danger-lt',
                    ],

                    'expired' => [
                        'label' => 'Kadaluarsa',
                        'class' => 'bg-secondary-lt',
                    ],
                ];

                $currentStatus = $statusMap[$transaction->status]
                    ?? [
                        'label' => $transaction->status,
                        'class' => 'bg-secondary-lt',
                    ];

                return '<span class="badge '
                    . $currentStatus['class']
                    . '">'
                    . e($currentStatus['label'])
                    . '</span>';
            })

            ->addColumn('action', function ($transaction) {

                $showRoute = route('admin.transactions.show', $transaction->id);
                $editRoute = route('admin.transactions.edit', $transaction->id);
                $deleteRoute = route('admin.transactions.destroy', $transaction->id);

                $html = '<div class="btn-list flex-nowrap">';

                $html .= '
                    <a href="' . $showRoute . '"
                    class="btn btn-sm btn-primary"
                    title="Lihat Detail">
                        <i class="ti ti-eye"></i>
                    </a>
                ';

                if ($transaction->status === 'waiting_confirmation') {
                    $html .= '
                        <a href="' . $showRoute . '"
                        class="btn btn-sm btn-outline-warning"
                        title="Perlu Verifikasi">
                            <i class="ti ti-clock-check"></i>
                        </a>
                    ';
                }

                // Edit: boleh Super-Admin & Tim (samakan dengan izin route index/show)
                $html .= '
                    <a href="' . $editRoute . '"
                    class="btn btn-sm btn-outline-secondary"
                    title="Edit Transaksi">
                        <i class="ti ti-edit"></i>
                    </a>
                ';

                // Delete: hanya Super-Admin & status bukan paid
                if (
                    auth()->user()->hasRole('Super-Admin')
                    && $transaction->status !== 'paid'
                ) {
                    $html .= '
                        <button type="button"
                                class="btn btn-sm btn-outline-danger btn-delete-transaction"
                                data-url="' . $deleteRoute . '"
                                title="Hapus Transaksi">
                            <i class="ti ti-trash"></i>
                        </button>
                    ';
                }

                $html .= '</div>';

                return $html;
            })

            ->rawColumns([
                'participants',
                'status_badge',
                'action',
            ])

            ->make(true);
    }

    return view('admin.index', compact('status'));
}

    public function show(Transaction $transaction)
    {
        $this->authorizeStaff();
        $transaction->load(['event', 'registeredBy', 'registrations.user']);

        return view('admin.show', compact('transaction'));
    }

    public function approve(Transaction $transaction)
    {
        $this->authorizeStaff();
        // Cek cepat (pengaman sebenarnya ada di dalam lock di bawah)
        abort_unless(
            $transaction->status === 'waiting_confirmation',
            400,
            'Transaksi ini tidak dalam status menunggu konfirmasi.'
        );
        abort_if(
            $transaction->registered_by === auth()->id(),
            403,
            'Kamu tidak bisa menyetujui transaksi yang kamu daftarkan sendiri.'
        );
        DB::transaction(function () use ($transaction) {

            // Kunci baris transaksi supaya approve bersamaan tidak lolos dua kali
            $locked = Transaction::whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $locked->status === 'waiting_confirmation',
                400,
                'Transaksi ini tidak dalam status menunggu konfirmasi.'
            );

            $locked->load(['event', 'registrations.user']);

            $event = $locked->event;

            abort_unless($event, 404, 'Event transaksi tidak ditemukan.');

            abort_unless(
                $event->cash_account_id,
                422,
                'Akun Kas / Bank untuk event ini belum ditentukan.'
            );

            abort_unless(
                $event->income_account_id,
                422,
                'Akun Pendapatan untuk event ini belum ditentukan.'
            );

            $licenseId = config('app.license_id');

            $akunKas = AccountingAccount::where('id', $event->cash_account_id)
                ->where('license_id', $licenseId)
                ->where('is_active', true)
                ->firstOrFail();

            $akunPendapatanEvent = AccountingAccount::where('id', $event->income_account_id)
                ->where('license_id', $licenseId)
                ->where('is_active', true)
                ->firstOrFail();

            // Bandingkan dalam satuan sen (integer), tanpa bcmath
            $totalPeserta = $locked->registrations
                ->sum(fn ($r) => (int) round((float) $r->price * 100));

            $nominalTransaksi = (int) round((float) $locked->total_amount * 100);

            abort_unless(
                $totalPeserta === $nominalTransaksi,
                422,
                'Total pembayaran peserta tidak sesuai dengan nominal transaksi.'
            );

            $locked->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $locked->registrations()->update([
                'status' => 'paid',
            ]);

            $journal = AccountingJournal::create([
                'license_id'       => $licenseId,
                'journal_code'     => $this->generateNextJournalCode(),
                'transaction_date' => now()->toDateString(),
                'description'      => 'Penerimaan pembayaran event - ' . $event->name,
                'created_by'       => auth()->id(),
                'reference_code'   => $locked->transaction_code,
                'transaction_id'   => $locked->id,
            ]);

            foreach ($locked->registrations as $registration) {

                $person = $registration->user->fullname
                    ?? $registration->guest_name
                    ?? $registration->ticket_code;

                $nominalPeserta = round((float) $registration->price, 2);

                // Debit Kas / Bank
                AccountingJournalDetail::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $akunKas->id,
                    'person'      => $person,
                    'debit'       => $nominalPeserta,
                    'credit'      => 0,
                    'description' => 'Penerimaan pembayaran event - '
                                    . $event->name . ' - ' . $registration->ticket_code,
                ]);

                // Kredit Pendapatan Event
                AccountingJournalDetail::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $akunPendapatanEvent->id,
                    'person'      => $person,
                    'debit'       => 0,
                    'credit'      => $nominalPeserta,
                    'description' => 'Pendapatan pendaftaran event - '
                                    . $event->name . ' - ' . $registration->ticket_code,
                ]);
            }
        });

        // Muat ulang supaya status 'paid' terbaca di notifikasi
        $transaction->refresh()->load([
            'event',
            'registrations.user',
            'registeredBy',
        ]);

        if ($transaction->registeredBy) {
            try {
                $transaction->registeredBy->notify(
                    new TransactionApprovedNotification($transaction)
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with(
            'success',
            'Transaksi berhasil disetujui, status diubah menjadi Lunas dan jurnal berhasil dibuat.'
        );
    }
    public function reject(Request $request, Transaction $transaction)
    {
        $this->authorizeStaff();
        abort_unless(
            $transaction->status === 'waiting_confirmation',
            400,
            'Transaksi ini tidak dalam status menunggu konfirmasi.'
        );

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $transaction->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);


        $transaction->load(['event', 'registeredBy']);

        if ($transaction->registeredBy) {
            try {
                $transaction->registeredBy->notify(new TransactionRejectedNotification($transaction));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'Transaksi ditolak, user perlu upload ulang bukti transfer.');
    }

private function generateNextJournalCode(): string
{
    $licenseId = config('app.license_id');

    // Serialisasi pembuatan kode per license. Lepas otomatis saat transaksi selesai.
    DB::select('SELECT pg_advisory_xact_lock(hashtext(?))', ['journal_code:' . $licenseId]);

    $last = AccountingJournal::where('license_id', $licenseId)
        ->whereRaw("journal_code ~ '^IJ-[0-9]+$'")
        ->selectRaw('MAX(CAST(SUBSTRING(journal_code FROM 4) AS INTEGER)) as last_number')
        ->value('last_number');

    return 'IJ-' . str_pad(((int) $last) + 1, 4, '0', STR_PAD_LEFT);
}

public function edit(Transaction $transaction)
{
    $transaction->load(['event', 'registeredBy', 'registrations.user']);

    return view('admin.transactions.edit', compact('transaction'));
}

public function update(Request $request, Transaction $transaction)
{
    $validated = $request->validate([
        'status' => 'required|in:pending,waiting_confirmation,paid,rejected,expired',
        'total_amount' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:1000',
    ]);

    $transaction->update($validated);

    return redirect()
        ->route('admin.transactions.index')
        ->with('success', 'Transaksi berhasil diperbarui.');
}

public function destroy(Transaction $transaction)
{
    if ($transaction->status === 'paid') {
        return response()->json([
            'success' => false,
            'message' => 'Transaksi yang sudah lunas tidak dapat dihapus.',
        ], 422);
    }

    // Hapus relasi terkait dulu jika belum ada cascade di migration
    // $transaction->registrations()->delete();

    $transaction->delete();

    return response()->json([
        'success' => true,
        'message' => 'Transaksi berhasil dihapus.',
    ]);
}
}