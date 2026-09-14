<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TransactionApprovedNotification extends Notification
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

        return (new MailMessage)
            ->subject('Pembayaran Dikonfirmasi - ' . $transaction->transaction_code)
            ->greeting('Halo, ' . ($notifiable->fullname ?? $notifiable->name) . '!')
            ->line('Pembayaran untuk pendaftaran event berikut telah kami konfirmasi.')
            ->line('**Event:** ' . $transaction->event->name)
            ->line('**No. Transaksi:** ' . $transaction->transaction_code)
            ->line('**Total:** Rp ' . number_format($transaction->total_amount, 0, ',', '.'))
            ->line('**Jumlah Peserta:** ' . $transaction->registrations->count())
            ->action('Lihat Detail Transaksi', route('transactions.show', $transaction->id))
            ->line('Terima kasih sudah mendaftar. Sampai jumpa di acaranya!');
    }
}