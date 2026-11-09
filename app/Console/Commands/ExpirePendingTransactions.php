<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirePendingTransactions extends Command
{
    protected $signature = 'transactions:expire-pending';
    protected $description = 'Ubah transaksi pending yang melewati batas waktu menjadi expired';

    public function handle(): int
    {
        $count = 0;

        Transaction::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($transactions) use (&$count) {
                foreach ($transactions as $transaction) {
                    DB::transaction(function () use ($transaction, &$count) {
                        // Kunci & cek ulang, jaga-jaga user baru saja upload bukti
                        $locked = Transaction::whereKey($transaction->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $locked || $locked->status !== 'pending') {
                            return;
                        }

                        $locked->update(['status' => 'expired']);
                        $locked->registrations()->update(['status' => 'cancelled']);
                        $count++;
                    });
                }
            });

        $this->info("{$count} transaksi di-expire.");

        return self::SUCCESS;
    }
}