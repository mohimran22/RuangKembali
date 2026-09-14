<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\TransactionApprovedNotification;
use App\Notifications\TransactionRejectedNotification;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $transactions = Transaction::with('event', 'registeredBy')
            ->withCount('registrations')
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.index', compact('transactions', 'status'));
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

        $transaction->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Ikut update status semua registrasi peserta di dalamnya
        $transaction->registrations()->update(['status' => 'paid']);

            $transaction->load(['event', 'registrations.user', 'registeredBy']);

    // Kirim ke user yang submit form (pendaftar)
    $transaction->registeredBy->notify(new TransactionApprovedNotification($transaction));

        return back()->with('success', 'Transaksi berhasil disetujui, status diubah menjadi Lunas.');
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
}