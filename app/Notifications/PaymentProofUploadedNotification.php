<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Storage;

class PaymentProofUploadedNotification extends Notification
{
    public function __construct(
        public Transaction $transaction
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    private function rupiah($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    public function toMail($notifiable): MailMessage
    {
        $t     = $this->transaction;
        $event = $t->event;
        $proof = $t->proof_of_payment;

        $mail = (new MailMessage)
            ->subject('Bukti Pembayaran Diterima - ' . $t->transaction_code)
            ->greeting('Assalamualaikum, ' . ($notifiable->fullname ?? $notifiable->name) . '!')
            ->line('Terima kasih sudah melakukan pembayaran.')
            ->line('Bukti pembayaran kamu sudah kami terima dan terlampir pada email ini.')

            // ===== Ringkasan =====
            ->line('## Ringkasan')
            ->line('**Event:** ' . $event->name)
            ->line('**No. Transaksi:** ' . $t->transaction_code)
            ->line('**Status:** Menunggu Konfirmasi')

            // ===== Daftar peserta =====
            ->line('## Daftar Peserta (' . $t->registrations->count() . ')');

        foreach ($t->registrations as $r) {
            $mail->line(
                '**' . $r->participant_name . '**  ' . "\n" .
                $r->participant_email . '  ' . "\n" .
                'Kode Tiket: ' . $r->ticket_code . '  ' . "\n" .
                'Harga: ' . $this->rupiah($r->price)
            );
        }

        $mail->line('**Total Pembayaran: ' . $this->rupiah($t->total_amount) . '**');

        // ===== Lampiran bukti pembayaran =====
// ===== Lampiran bukti pembayaran =====
if ($proof && Storage::disk('public')->exists($proof)) {
    $mail->attach(
        Storage::disk('public')->path($proof),
        [
            'as'   => 'bukti-pembayaran-' . $t->transaction_code . '.' . pathinfo($proof, PATHINFO_EXTENSION),
            'mime' => Storage::disk('public')->mimeType($proof),
        ]
    );
}

        return $mail
            ->line('Tim Ruka akan segera mengecek pembayaran kamu. Kami akan mengirim email lagi setelah pembayaran dikonfirmasi.')
            ->action('Lihat Detail Transaksi', route('transactions.show', $t->id));
    }
}