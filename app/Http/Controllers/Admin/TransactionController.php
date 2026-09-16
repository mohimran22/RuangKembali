<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\TransactionApprovedNotification;
use App\Notifications\TransactionRejectedNotification;
use App\Models\Transaction;
use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
public function index(Request $request)
{
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
                        return $registration->user->fullname ?? '-';
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
        $transaction->load(['event', 'registeredBy', 'registrations.user']);

        return view('admin.show', compact('transaction'));
    }

    public function approve(Transaction $transaction)
    {
        abort_unless(
            $transaction->status === 'waiting_confirmation',
            400,
            'Transaksi ini tidak dalam status menunggu konfirmasi.'
        );

        DB::transaction(function () use ($transaction) {

            // Load event beserta COA yang sudah ditentukan pada event
            $transaction->load('event');

            $event = $transaction->event;

            abort_unless(
                $event,
                404,
                'Event transaksi tidak ditemukan.'
            );

            // Pastikan Event sudah memiliki COA Kas/Bank
            abort_unless(
                $event->cash_account_id,
                422,
                'Akun Kas / Bank untuk event ini belum ditentukan.'
            );

            // Pastikan Event sudah memiliki COA Pendapatan
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

            $totalPeserta = $transaction->registrations->sum(function ($registration) {
                return (float) $registration->price;
            });

            $nominalTransaksi = (float) $transaction->total_amount;

            abort_unless(
                bccomp((string) $totalPeserta, (string) $nominalTransaksi, 2) === 0,
                422,
                'Total pembayaran peserta tidak sesuai dengan nominal transaksi.'
            );

            $transaction->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $transaction->registrations()->update([
                'status' => 'paid',
            ]);

            $journalCode = $this->generateNextJournalCode();

            $journal = AccountingJournal::create([
                'license_id'       => $licenseId,
                'journal_code'     => $journalCode,
                'transaction_date' => now()->toDateString(),
                'description'      => 'Penerimaan pembayaran event - '
                                    . ($transaction->event->name),
                'created_by'       => auth()->id(),
                'reference_code'   => $transaction->transaction_code,
                'transaction_id'   => $transaction->id,
            ]);

            foreach ($transaction->registrations as $registration) {

                $registration->loadMissing('user');

                $person = $registration->user->fullname
                    ?? $registration->guest_name
                    ?? $registration->ticket_code;

                $nominalPeserta = (float) $registration->price;

                // Debit Kas / Bank
                AccountingJournalDetail::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $akunKas->id,
                    'person'      => $person,
                    'debit'       => $nominalPeserta,
                    'credit'      => 0,
                    'description' => 'Penerimaan pembayaran event - '
                                    . $event->name
                                    . ' - '
                                    . $registration->ticket_code,
                ]);

                // Kredit Pendapatan Event
                AccountingJournalDetail::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $akunPendapatanEvent->id,
                    'person'      => $person,
                    'debit'       => 0,
                    'credit'      => $nominalPeserta,
                    'description' => 'Pendapatan pendaftaran event - '
                                    . $event->name
                                    . ' - '
                                    . $registration->ticket_code,
                ]);
            }
        });

        $transaction->load([
            'event',
            'registrations.user',
            'registeredBy',
        ]);

        if ($transaction->registeredBy) {
            $transaction->registeredBy->notify(
                new TransactionApprovedNotification($transaction)
            );
        }

        return back()->with(
            'success',
            'Transaksi berhasil disetujui, status diubah menjadi Lunas dan jurnal berhasil dibuat.'
        );
    }

    public function reject(Request $request, Transaction $transaction)
    {
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

    $transaction->registeredBy->notify(new TransactionRejectedNotification($transaction));

        return back()->with('success', 'Transaksi ditolak, user perlu upload ulang bukti transfer.');
    }

    private function generateNextJournalCode()
{
    $licenseId = config('app.license_id');

    $lastJournalNumber = AccountingJournal::where('license_id', $licenseId)
        ->where('journal_code', 'ILIKE', 'IJ-%')
        ->selectRaw("
            MAX(
                CAST(
                    REGEXP_REPLACE(journal_code, '^.*-', '') AS INTEGER
                )
            ) as last_number
        ")
        ->value('last_number');

    $lastJournalNumber = $lastJournalNumber ?? 0;

    do {
        $nextNumber = str_pad($lastJournalNumber + 1, 4, '0', STR_PAD_LEFT);
        $journalCode = 'IJ-' . $nextNumber;

        $exists = AccountingJournal::where('journal_code', $journalCode)->exists();
        $lastJournalNumber++;
    } while ($exists);

    return $journalCode;
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