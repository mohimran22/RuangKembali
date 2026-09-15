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
                                . ($transaction->transaction_number ?? $transaction->event->name),
            'created_by'       => auth()->id(),
        ]);

        foreach ($transaction->registrations as $registration) {

            $registration->loadMissing('user');

            $person = $registration->user->fullname
                ?? $registration->user->name
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
}