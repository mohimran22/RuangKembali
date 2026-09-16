<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
// public function index(Request $request)
// {
//     $query = Transaction::with([
//         'event',
//         'registeredBy',
//         'registrations.user:id,fullname'
//     ])
//     ->withCount('registrations')
//     ->latest();

//     if (!auth()->user()->hasAnyRole(['Super-Admin', 'Tim'])) {
//         $query->where(function ($q) {
//             $q->where('registered_by', auth()->id())
//               ->orWhereHas('registrations', function ($registrationQuery) {
//                   $registrationQuery->where('user_id', auth()->id());
//               });
//         });
//     }

//     $transactions = $query->paginate(10);

//     return view('transactions.index', compact('transactions'));
// }
public function index(Request $request)
{
    $query = Transaction::with([
        'event',
        'registeredBy',
        'registrations.user:id,fullname',
    ])
    ->withCount('registrations')
    ->latest();

    if (!auth()->user()->hasAnyRole(['Super-Admin', 'Tim'])) {
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

                if ($transaction->registrations_count == 0) {
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

                $currentStatus = $statusMap[$transaction->status]
                    ?? [
                        'label' => $transaction->status,
                        'class' => 'bg-secondary',
                    ];

                return '<span class="badge '
                    . $currentStatus['class']
                    . '">'
                    . e($currentStatus['label'])
                    . '</span>';
            })

            ->addColumn('action', function ($transaction) {

                $isAdmin = auth()->user()->hasAnyRole(['Super-Admin', 'Tim']);

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
                    $editRoute = route('admin.transactions.edit', $transaction->id);

                    $html .= '
                        <a href="' . $editRoute . '"
                        class="btn btn-sm btn-outline-secondary"
                        title="Edit Transaksi">
                            <i class="ti ti-edit"></i>
                        </a>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger btn-delete-transaction"
                                data-id="' . $transaction->id . '"
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

    return view('transactions.index');
}
public function show(Transaction $transaction)
{
    $user = auth()->user();

    $hasAccess = $user->hasAnyRole(['Super-Admin', 'Tim'])
        || $transaction->registered_by === $user->id
        || $transaction->registrations()
            ->where('user_id', $user->id)
            ->exists();

    abort_unless(
        $hasAccess,
        403,
        'Kamu tidak memiliki akses ke transaksi ini.'
    );

    $transaction->load([
        'event',
        'registrations.user'
    ]);

    return view('transactions.show', compact('transaction'));
}

public function uploadProof(Request $request, Transaction $transaction)
{
    $user = auth()->user();

    $hasAccess = $user->hasAnyRole(['Super-Admin', 'Tim'])
        || $transaction->registered_by === $user->id
        || $transaction->registrations()
            ->where('user_id', $user->id)
            ->exists();

    abort_unless(
        $hasAccess,
        403,
        'Kamu tidak memiliki akses ke transaksi ini.'
    );

    abort_unless(
        $transaction->status === 'pending',
        400,
        'Transaksi ini sudah tidak bisa diupload buktinya.'
    );

    $request->validate([
        'proof_of_payment' => [
            'required',
            'image',
            'max:5120',
        ],
    ]);

    $path = $request->file('proof_of_payment')
        ->store('proof-of-payments', 'public');

    $transaction->update([
        'proof_of_payment' => $path,
        'status' => 'waiting_confirmation',
    ]);

    return back()->with(
        'success',
        'Bukti transfer berhasil diupload, menunggu konfirmasi admin.'
    );
}
}