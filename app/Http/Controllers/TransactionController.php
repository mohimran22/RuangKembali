<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
public function index(Request $request)
{
    $query = Transaction::with([
        'event',
        'registeredBy',
        'registrations.user:id,fullname'
    ])
    ->withCount('registrations')
    ->latest();

    if (!auth()->user()->hasAnyRole(['Super-Admin', 'Tim'])) {
        $query->where(function ($q) {
            $q->where('registered_by', auth()->id())
              ->orWhereHas('registrations', function ($registrationQuery) {
                  $registrationQuery->where('user_id', auth()->id());
              });
        });
    }

    $transactions = $query->paginate(10);

    return view('transactions.index', compact('transactions'));
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