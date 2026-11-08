<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventRegistrationSuccessNotification extends Notification
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

    private function statusLabel(string $status): string
    {
        return [
            'pending'              => 'Menunggu Pembayaran',
            'waiting_confirmation' => 'Menunggu Konfirmasi',
            'paid'                 => 'Lunas',
            'rejected'             => 'Ditolak',
            'expired'              => 'Kadaluarsa',
        ][$status] ?? $status;
    }

    public function toMail($notifiable): MailMessage
    {
        $t      = $this->transaction;
        $event  = $t->event;
        $isPaid = $t->status === 'paid';
        $url    = route('transactions.show', $t->id);

        $mail = (new MailMessage)
            ->subject('Pendaftaran Event Berhasil - ' . $t->transaction_code)
            ->greeting('Assalamualaikum, ' . ($notifiable->fullname ?? $notifiable->name) . '!')
            ->line('Terima kasih sudah mendaftar di event ini.')

            // ===== Ringkasan =====
            ->line('## Ringkasan')
            ->line('**Event:** ' . $event->name)
            ->line('**No. Transaksi:** ' . $t->transaction_code)
            ->line('**Status:** ' . $this->statusLabel($t->status))

            // ===== Daftar peserta =====
            ->line('## Daftar Peserta (' . $t->registrations->count() . ')');

        foreach ($t->registrations as $r) {
            $mail->line(
                '**' . $r->participant_name . '**'
                    . (! $r->user_id ? ' _(belum punya akun)_' : '') . '  ' . "\n" .
                $r->participant_email . '  ' . "\n" .
                'Kode Tiket: ' . $r->ticket_code . '  ' . "\n" .
                'Harga: ' . $this->rupiah($r->price)
            );
        }

        $mail->line('**Total Harga: ' . $this->rupiah($t->total_amount) . '**');

        // ===== Pembayaran =====
        if ($isPaid) {
            return $mail
                ->line('Event ini gratis, jadi pendaftaran kamu sudah langsung terkonfirmasi. Sampai jumpa di acaranya!')
                ->action('Lihat Detail Transaksi', $url);
        }

        $mail->line('## Pembayaran');

        if ($t->payment_method === 'transfer') {
            if ($event->transfer_account_number) {
                $mail
                    ->line('Selesaikan pembayaran sesuai metode transfer. Silakan transfer ke rekening berikut:')
                    ->line(
                        '**' . $event->transfer_bank_name . '**  ' . "\n" .
                        '**' . $event->transfer_account_number . '**  ' . "\n" .
                        'a.n. ' . $event->transfer_account_holder . '  ' . "\n" .
                        'Nominal: **' . $this->rupiah($t->total_amount) . '**'
                    )
                    ->line('Segera kirim dan upload bukti pembayaran (format gambar, maksimal 5 MB) melalui halaman detail transaksi.');
            } else {
                $mail->line('Informasi rekening untuk event ini belum diatur panitia. Silakan hubungi panitia event sebelum melakukan pembayaran.');
            }
        } elseif ($t->payment_method === 'gateway') {
            $mail->line('Kamu memilih Payment Gateway. Silakan lanjutkan pembayaran melalui halaman detail transaksi.');
        }

        return $mail
            ->action('Upload Bukti Pembayaran', $url)
            ->line('Kami akan mengirim email lagi setelah pembayaran kamu dikonfirmasi.');
    }
}