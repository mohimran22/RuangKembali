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

        $nominal = (float) $transaction->amount;

        $transaction->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        $transaction->registrations()->update([
            'status' => 'paid',
        ]);

        $journalCode = 'JEV-' . now()->format('YmdHis')
            . '-' . strtoupper(Str::random(4));

        $journal = AccountingJournal::create([
            'license_id'       => $licenseId,
            'journal_code'     => $journalCode,
            'transaction_date' => now()->toDateString(),
            'description'      => 'Penerimaan pembayaran event - '
                                . ($transaction->transaction_number ?? $transaction->id),
            'created_by'       => auth()->id(),
        ]);

        AccountingJournalDetail::create([
            'journal_id'  => $journal->id,
            'account_id'  => $akunKas->id,
            'person'      => null,
            'debit'       => $nominal,
            'credit'      => 0,
            'description' => 'Penerimaan pembayaran event - ' . $event->name,
        ]);


        AccountingJournalDetail::create([
            'journal_id'  => $journal->id,
            'account_id'  => $akunPendapatanEvent->id,
            'person'      => null,
            'debit'       => 0,
            'credit'      => $nominal,
            'description' => 'Pendapatan pendaftaran event - ' . $event->name,
        ]);
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
}