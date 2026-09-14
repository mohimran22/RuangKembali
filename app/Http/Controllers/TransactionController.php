<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
public function index(Request $request)
{
    $query = Transaction::with(['event', 'registeredBy','registrations.user:id,fullname'])
        ->withCount('registrations')
        ->latest();

    if (!auth()->user()->hasRole('Super-Admin')) {
        $query->where('registered_by', auth()->id());
    }

    $transactions = $query->paginate(10);

    return view('transactions.index', compact('transactions'));
}

    public function show(Transaction $transaction)
    {
        // Pastikan user cuma bisa lihat transaksinya sendiri
        abort_unless(
            $transaction->registered_by === auth()->id(),
            403,
            'Kamu tidak memiliki akses ke transaksi ini.'
        );

        $transaction->load(['event', 'registrations.user']);

        return view('transactions.show', compact('transaction'));
    }

    public function uploadProof(Request $request, Transaction $transaction)
    {
        abort_unless(
            $transaction->registered_by === auth()->id(),
            403,
            'Kamu tidak memiliki akses ke transaksi ini.'
        );

        abort_unless(
            $transaction->status === 'pending',
            400,
            'Transaksi ini sudah tidak bisa diupload buktinya.'
        );

        $request->validate([
            'proof_of_payment' => ['required', 'image', 'max:5120'], // max 5MB
        ]);

        $path = $request->file('proof_of_payment')->store('proof-of-payments', 'public');

        $transaction->update([
            'proof_of_payment' => $path,
            'status' => 'waiting_confirmation',
        ]);

        return back()->with('success', 'Bukti transfer berhasil diupload, menunggu konfirmasi admin.');
    }
}