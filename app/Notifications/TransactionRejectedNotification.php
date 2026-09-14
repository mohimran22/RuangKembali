<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TransactionRejectedNotification extends Notification
{

    public function __construct(
        public Transaction $transaction
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $transaction = $this->transaction;

        $message = (new MailMessage)
            ->subject('Bukti Transfer Ditolak - ' . $transaction->transaction_code)
            ->greeting('Halo, ' . ($notifiable->fullname ?? $notifiable->name) . '!')
            ->line('Mohon maaf, bukti transfer yang kamu upload untuk transaksi berikut belum bisa kami konfirmasi.')
            ->line('**Event:** ' . $transaction->event->name)
            ->line('**No. Transaksi:** ' . $transaction->transaction_code)
            ->line('**Total:** Rp ' . number_format($transaction->total_amount, 0, ',', '.'));

        if ($transaction->rejection_reason) {
            $message->line('**Alasan:** ' . $transaction->rejection_reason);
        }

        return $message
            ->action('Upload Ulang Bukti Transfer', route('transactions.show', $transaction->id))
            ->line('Silakan upload ulang bukti transfer yang valid melalui halaman transaksi kamu.');
    }
}