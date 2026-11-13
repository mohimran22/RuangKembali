<?php

namespace App\Http\Controllers;

use App\Helpers\ActiveRole;
use App\Models\Transaction;
use App\Notifications\PaymentProofUploadedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
private function isStaff(): bool
{
    return in_array(ActiveRole::name(), ['Super-Admin', 'Tim']);
}

private function hasAccessTo(Transaction $transaction): bool
{
    $userId = auth()->id();

    return $this->isStaff()
        || $transaction->registered_by === $userId
        || $transaction->registrations()->where('user_id', $userId)->exists();
}

private function authorizeAdminOrTim(): void
{
    abort_unless(
        $this->isStaff(),
        403,
        'Kamu tidak memiliki akses untuk melakukan aksi ini.'
    );
}
public function index(Request $request)
{
    $query = Transaction::with([
        'event',
        'registeredBy',
        'registrations.user:id,fullname',
    ])
    ->withCount('registrations')
    ->latest();

    if (!$this->isStaff()) {
        $query->where(function ($q) {
            $q->where('registered_by', auth()->id())
              ->orWhereHas('registrations', function ($registrationQuery) {
                  $registrationQuery->where(
                      'user_id',
                      auth()->id()
                  );
              });
        });
    }
    if ($request->boolean('guest')) {
        $query->whereHas('registrations', fn ($r) => $r->whereNull('user_id'));
    }
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

            // ->addColumn('participants', function ($transaction) {

            //     if ($transaction->registrations_count == 0) {
            //         return '0';
            //     }

            //     $names = $transaction->registrations
            //         ->map(function ($registration) {
            //             return $registration->participant_name ?? '-';
            //         })
            //         ->filter()
            //         ->implode(', ');

            //     return $transaction->registrations_count
            //         . ' <i class="ti ti-info-circle text-secondary ms-1"
            //                 data-bs-toggle="tooltip"
            //                 title="' . e($names) . '"></i>';
            // })
            ->addColumn('participants', function ($transaction) {

            if ($transaction->registrations_count == 0) {
                return '0';
            }

    $names = $transaction->registrations
        ->map(fn ($registration) => $registration->participant_name ?? '-')
        ->filter()
        ->implode(', ');

    $guestCount = $transaction->registrations->whereNull('user_id')->count();

    return $transaction->registrations_count
        . ' <i class="ti ti-info-circle text-secondary ms-1"
                data-bs-toggle="tooltip"
                title="' . e($names) . '"></i>'
        . ($guestCount > 0
            ? ' <span class="badge bg-orange ms-1">' . $guestCount . ' tamu</span>'
            : '');
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
                        'class' => 'bg-warning',
                    ],

                    'waiting_confirmation' => [
                        'label' => 'Menunggu Konfirmasi',
                        'class' => 'bg-blue',
                    ],

                    'paid' => [
                        'label' => 'Lunas',
                        'class' => 'bg-success',
                    ],

                    'rejected' => [
                        'label' => 'Ditolak',
                        'class' => 'bg-danger',
                    ],

                    'expired' => [
                        'label' => 'Kadaluarsa',
                        'class' => 'bg-secondary',
                    ],
                ];

                $currentStatus = $statusMap[$transaction->display_status]
                    ?? [
                        'label' => $transaction->display_status,
                        'class' => 'bg-secondary',
                    ];

                return '<span class="badge '
                    . $currentStatus['class']
                    . '">'
                    . e($currentStatus['label'])
                    . '</span>';
            })

            ->addColumn('action', function ($transaction) {
               
                $isAdmin = $this->isStaff();

                $showRoute = $isAdmin
                    ? route('admin.transactions.show', $transaction->id)
                    : route('transactions.show', $transaction->id);

                $html = '<div class="btn-list flex-nowrap">';

                $html .= '
                    <a href="' . $showRoute . '"
                    class="btn btn-sm btn-primary"
                    title="Lihat Detail">
                        <i class="ti ti-eye"></i>
                    </a>
                ';

                if ($isAdmin && $transaction->status === 'waiting_confirmation') {
                    $html .= '
                        <a href="' . $showRoute . '"
                        class="btn btn-sm btn-outline-warning"
                        title="Perlu Verifikasi">
                            <i class="ti ti-clock-check"></i>
                        </a>
                    ';
                }

                if ($isAdmin) {
                    $isPaid     = $transaction->status === 'paid';
                    $canDelete  = !$isPaid || ActiveRole::name() === 'Super-Admin';

                    if (!$isPaid && !$transaction->is_expired) {
                        $editRoute = route('transactions.edit', $transaction->id);

                        $html .= '
                            <a href="' . $editRoute . '"
                            class="btn btn-sm btn-secondary"
                            title="Edit Transaksi">
                                <i class="ti ti-edit"></i>
                            </a>
                        ';
                    }

                    if ($canDelete) {
                        $html .= '
                            <button type="button"
                                    class="btn btn-sm btn-danger btn-delete-transaction"
                                    data-id="' . $transaction->id . '"
                                    data-paid="' . ($isPaid ? 1 : 0) . '"
                                    title="Hapus Transaksi">
                                <i class="ti ti-trash"></i>
                            </button>
                        ';
                    }
                }
                $html .= '</div>';

                return $html;
            })
            ->filterColumn('participants', function ($query, $keyword) {
                $term = '%' . addcslashes($keyword, '%_\\') . '%';

                $query->whereHas('registrations', function ($r) use ($term) {
                    $r->where('guest_name', 'ilike', $term)
                    ->orWhere('guest_email', 'ilike', $term)
                    ->orWhere('ticket_code', 'ilike', $term)
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('fullname', 'ilike', $term)
                        ->orWhere('email', 'ilike', $term));
                });
            })
            ->rawColumns([
                'participants',
                'status_badge',
                'action',
            ])

            ->make(true);
    }

    return view('transactions.index', ['isStaff' => $this->isStaff()]);
}
public function show(Transaction $transaction)
{
    $user = auth()->user();

    abort_unless($this->hasAccessTo($transaction), 403, 'Kamu tidak memiliki akses ke transaksi ini.');

    $transaction->load([
        'event',
        'registrations.user'
    ]);

    return view('transactions.show', compact('transaction'));
}

public function uploadProof(Request $request, Transaction $transaction)
{
    abort_unless(
        $this->hasAccessTo($transaction),
        403,
        'Kamu tidak memiliki akses ke transaksi ini.'
    );

    abort_unless(
        in_array($transaction->status, ['pending', 'rejected']),
        400,
        'Transaksi ini sudah tidak bisa diupload buktinya.'
    );

        abort_if(
            $transaction->is_expired,
            400,
            'Batas waktu pembayaran sudah lewat. Silakan daftar ulang ke event ini.'
        );

    $request->validate([
        'proof_of_payment' => ['required', 'image', 'max:5120'],
    ]);

    $oldProof = $transaction->proof_of_payment;

    $path = $request->file('proof_of_payment')
        ->store('proof-of-payments', 'public');

    $transaction->update([
        'proof_of_payment' => $path,
        'proof_uploaded_by' => auth()->id(),
        'status'           => 'waiting_confirmation',
        'rejection_reason' => null,
    ]);

    // Bukti lama (yang ditolak) dibuang supaya tidak menumpuk
    if ($oldProof && Storage::disk('public')->exists($oldProof)) {
        Storage::disk('public')->delete($oldProof);
    }
    try {
        $transaction->refresh()->load(['event', 'registrations.user', 'registeredBy']);

        if ($transaction->registeredBy) {
            $transaction->registeredBy->notify(
                new PaymentProofUploadedNotification($transaction)
            );
        }
    } catch (\Throwable $e) {
        report($e);
    }

    return back()->with(
        'success',
        'Bukti transfer berhasil diupload, menunggu konfirmasi admin.'
    );
}

public function edit(Transaction $transaction)
{
    $this->authorizeAdminOrTim();
    abort_if($transaction->status === 'paid', 422, 'Transaksi yang sudah lunas tidak bisa diubah.');
      abort_if($transaction->is_expired, 422, 'Transaksi yang sudah kadaluarsa tidak bisa diubah.');
    $transaction->load(['event', 'registeredBy', 'registrations.user']);

    return view('transactions.edit', compact('transaction'));
}

public function update(Request $request, Transaction $transaction)
{
    $this->authorizeAdminOrTim();
    abort_if($transaction->status === 'paid', 422, 'Transaksi yang sudah lunas tidak bisa diubah.');
      abort_if($transaction->is_expired, 422, 'Transaksi yang sudah kadaluarsa tidak bisa diubah.');
    $validated = $request->validate([
        'participants' => 'array',
        'participants.*.id' => 'required|uuid|exists:event_registrations,id',
        'participants.*.guest_name' => 'nullable|string|max:255',
        'participants.*.guest_email' => 'nullable|email|max:255',
        'participants.*.price' => 'required|numeric|min:0',
    ]);

    if (!empty($validated['participants'])) {
        foreach ($validated['participants'] as $participantData) {

            $registration = $transaction->registrations
                ->firstWhere('id', $participantData['id']);

            if (!$registration) {
                continue;
            }

            $updateData = [
                'price' => $participantData['price'],
            ];

            // Nama & email hanya untuk peserta guest (belum punya akun).
            if (is_null($registration->user_id)) {
                $updateData['guest_name'] = $participantData['guest_name'] ?? $registration->guest_name;
                $updateData['guest_email'] = $participantData['guest_email'] ?? $registration->guest_email;
            }

            $registration->update($updateData);
        }
    }

    // Hitung ulang total_amount dari harga peserta yang sudah diperbarui.
    $transaction->update([
        'total_amount' => $transaction->registrations()->sum('price'),
    ]);

    return redirect()
        ->route('transactions.index')
        ->with('success', 'Transaksi berhasil diperbarui.');
}
public function destroy(Request $request, Transaction $transaction)
{
    $this->authorizeAdminOrTim();

    $licenseId = config('app.license_id');
    $deletedJournals = 0;
    $deletedRegistrations = 0;
    $snapshot = [];

    DB::transaction(function () use ($request, $transaction, $licenseId, &$deletedJournals, &$deletedRegistrations, &$snapshot) {

        // Kunci baris dan cek ulang status di dalam kunci, supaya tidak
        // bentrok dengan approve yang berjalan bersamaan
        $locked = Transaction::whereKey($transaction->id)
            ->lockForUpdate()
            ->firstOrFail();

        $isPaid = $locked->status === 'paid';

        if ($isPaid) {
            abort_unless(
                ActiveRole::name() === 'Super-Admin',
                403,
                'Hanya Super-Admin yang boleh menghapus transaksi yang sudah lunas.'
            );

            $request->validate([
                'reason' => ['required', 'string', 'min:5', 'max:500'],
            ]);
        }

        // Peserta yang sudah hadir tidak boleh hilang dari catatan
        $hasAttended = $locked->registrations()
            ->where(function ($q) {
                $q->where('status', 'attended')->orWhereNotNull('checked_in_at');
            })
            ->exists();

        abort_if(
            $hasAttended,
            422,
            'Transaksi ini memiliki peserta yang sudah check-in, jadi tidak bisa dihapus.'
        );

        // Jurnal yang terhubung (lewat transaction_id, atau kode referensi untuk data lama)
        $journalIds = AccountingJournal::where('license_id', $licenseId)
            ->where(function ($q) use ($locked) {
                $q->where('transaction_id', $locked->id)
                  ->orWhere('reference_code', $locked->transaction_code);
            })
            ->pluck('id');

        $snapshot = [
            'transaction_code' => $locked->transaction_code,
            'status'           => $locked->status,
            'total_amount'     => $locked->total_amount,
            'event_id'         => $locked->event_id,
            'reason'           => $request->input('reason'),
            'deleted_by'       => auth()->id(),
            'journal_ids'      => $journalIds->all(),
        ];

        if ($journalIds->isNotEmpty()) {
            AccountingJournalDetail::whereIn('journal_id', $journalIds)->delete();
            $deletedJournals = AccountingJournal::whereIn('id', $journalIds)->delete();
        }

        $deletedRegistrations = $locked->registrations()->delete();

        $locked->delete();
    });

    if ($snapshot['status'] === 'paid') {
        Log::warning('Transaksi lunas dihapus', $snapshot + [
            'registrations_deleted' => $deletedRegistrations,
            'journals_deleted'      => $deletedJournals,
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => $deletedJournals > 0
            ? "Transaksi dihapus beserta {$deletedJournals} jurnal dan {$deletedRegistrations} peserta."
            : 'Transaksi berhasil dihapus.',
    ]);
}
// public function destroy(Transaction $transaction)
// {
//     $this->authorizeAdminOrTim();

//     $locked = $transaction->status === 'paid'
//         || $transaction->registrations()->where('status', 'attended')->exists();

//     if ($locked) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Transaksi yang sudah lunas atau pesertanya sudah check-in tidak bisa dihapus.',
//         ], 422);
//     }

//     \DB::transaction(function () use ($transaction) {
//         $transaction->registrations()->delete();
//         $transaction->delete();
//     });

//     return response()->json(['success' => true, 'message' => 'Transaksi berhasil dihapus.']);
// }

}