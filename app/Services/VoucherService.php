<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventVoucher;
use App\Models\Transaction;

class VoucherService
{
    /** Transaksi yang masih "memegang" voucher: lunas, menunggu konfirmasi, atau belum lewat tenggat. */
    private static function activeUsage(EventVoucher $voucher)
    {
        return Transaction::where('voucher_id', $voucher->id)
            ->where(function ($q) {
                $q->whereIn('status', ['paid', 'waiting_confirmation'])
                  ->orWhere(function ($w) {
                      $w->whereIn('status', ['pending', 'rejected'])
                        ->where(function ($e) {
                            $e->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        });
                  });
            });
    }

    public static function usedCount(EventVoucher $voucher): int
    {
        return self::activeUsage($voucher)->count();
    }

    public static function usedByUser(EventVoucher $voucher, $userId): int
    {
        return self::activeUsage($voucher)->where('registered_by', $userId)->count();
    }

    /**
     * @return array{voucher: ?EventVoucher, error: ?string, per_ticket_discount: float, net_price: float}
     */
    public static function resolve(string $code, Event $event, $userId, int $participants = 1, bool $lock = false): array
    {
        $fail = fn (string $msg) => ['voucher' => null, 'error' => $msg, 'per_ticket_discount' => 0.0, 'net_price' => (float) $event->price];

        if ($event->event_type === 'free' || (float) $event->price <= 0) {
            return $fail('Event ini gratis, voucher tidak diperlukan.');
        }

        $query = EventVoucher::whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))]);

        if ($lock) {
            $query->lockForUpdate();   // antrekan pendaftar bersamaan yang memakai voucher yang sama
        }

        $voucher = $query->first();

        if (! $voucher) {
            return $fail('Kode voucher tidak ditemukan.');
        }
        if ($voucher->event_id && $voucher->event_id !== $event->id) {
            return $fail('Voucher ini tidak berlaku untuk event ini.');
        }
        if (! $voucher->is_active) {
            return $fail('Voucher ini sedang tidak aktif.');
        }
        if ($voucher->valid_from && now()->lt($voucher->valid_from)) {
            return $fail('Voucher ini belum bisa dipakai.');
        }
        if ($voucher->valid_until && now()->gt($voucher->valid_until)) {
            return $fail('Voucher ini sudah kadaluarsa.');
        }
        if ($voucher->quota !== null && self::usedCount($voucher) >= $voucher->quota) {
            return $fail('Kuota voucher ini sudah habis.');
        }
        if ($voucher->per_user_limit && self::usedByUser($voucher, $userId) >= $voucher->per_user_limit) {
            return $fail('Kamu sudah mencapai batas pemakaian voucher ini.');
        }

        $price = (float) $event->price;

        if ($voucher->type === 'percent') {
            $discount = $price * ((float) $voucher->value) / 100;

            if ($voucher->max_discount) {
                $discount = min($discount, (float) $voucher->max_discount);
            }
        } else {
            $discount = (float) $voucher->value;
        }

        $discount = round(min($price, max(0, $discount)), 2);

        return [
            'voucher'             => $voucher,
            'error'               => null,
            'per_ticket_discount' => $discount,
            'net_price'           => $price - $discount,
        ];
    }
}