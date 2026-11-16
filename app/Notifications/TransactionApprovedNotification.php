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

        $mail = (new MailMessage)
            ->subject('Pembayaran Dikonfirmasi - ' . $transaction->transaction_code)
            ->greeting('Assalamualaikum, ' . ($notifiable->fullname ?? $notifiable->name) . '!')
            ->line('Pembayaran untuk pendaftaran event berikut telah kami konfirmasi.')
            ->line('**Event:** ' . $transaction->event->name)
            ->line('**No. Transaksi:** ' . $transaction->transaction_code)
            ->line('**Total:** Rp ' . number_format($transaction->total_amount, 0, ',', '.'))
            ->line('**Jumlah Peserta:** ' . $transaction->registrations->count());

        // Pengingat hanya jika gender belum diisi.
        if (!$notifiable->hasGender()) {
            $mail
                ->line('**Pengingat: Lengkapi Data Jenis Kelamin**')
                ->line(
                    'Untuk membantu Tim RuKa mengatur pembagian tempat duduk peserta laki-laki dan perempuan, '
                    . 'mohon lengkapi data jenis kelamin pada profil Anda.'
                )
                ->action('Lengkapi Data Profil', route('member.profile'));
        } else {
            $mail->action(
                'Lihat Detail Transaksi',
                route('transactions.show', $transaction->id)
            );
        }

        return $mail->line('Terima kasih sudah mendaftar. Sampai jumpa di acaranya!');
    }
}
