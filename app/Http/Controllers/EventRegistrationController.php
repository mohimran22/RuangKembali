<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventRegistrationController extends Controller
{
    public function create(Event $event)
    {
        abort_unless($event->is_published, 404);

        $user = auth()->user();
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();
        $transactionCode = $this->generateTransactionCode();
        return view('events.register', compact(
            'event',
            'registration',
            'transactionCode'
        ));
    }

    public function store(Request $request, Event $event)
    {
        abort_unless($event->is_published, 404);

        if ($event->registration_open && now()->lt($event->registration_open)) {
            return back()->with('error', 'Pendaftaran event belum dibuka.');
        }

        if ($event->registration_close && now()->gt($event->registration_close)) {
            return back()->with('error', 'Pendaftaran event sudah ditutup.');
        }

        $validated = $request->validate([
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'participants.*.name' => ['nullable', 'string', 'max:255'],
            'participants.*.email' => ['nullable', 'email', 'max:255'],
            'payment_method' => [
                $event->event_type !== 'free' ? 'required' : 'nullable',
                'in:transfer,gateway',
            ],
            'transaction_code' => ['nullable', 'string'],
        ]);
        foreach ($validated['participants'] as $p) {
            $hasUserId = !empty($p['user_id']);
            $hasGuestInfo = !empty($p['name']) && !empty($p['email']);

            if (!$hasUserId && !$hasGuestInfo) {
                return back()->withInput()->with('error', 'Setiap peserta harus berupa user terdaftar atau diisi nama & email.');
            }
        }
        $userIds = array_values(array_filter(array_column($validated['participants'], 'user_id')));
        $guestEmails = array_values(array_filter(array_map(
            fn($p) => !empty($p['email']) ? strtolower($p['email']) : null,
            $validated['participants']
        )));

        if (count($userIds) !== count(array_unique($userIds))) {
            return back()->withInput()->with('error', 'Terdapat peserta yang sama dipilih lebih dari sekali.');
        }

        if (count($guestEmails) !== count(array_unique($guestEmails))) {
            return back()->withInput()->with('error', 'Terdapat email peserta manual yang sama, cek kembali.');
        }

        if (!auth()->user()->hasRole('Super-Admin') && !in_array(auth()->id(), $userIds)) {
            return back()->withInput()->with('error', 'Kamu wajib ikut sebagai salah satu peserta.');
        }

        $alreadyRegistered = EventRegistration::where('event_id', $event->id)
            ->whereIn('user_id', $userIds)
            ->with('user:id,fullname')
            ->get();

        if ($alreadyRegistered->isNotEmpty()) {
            $names = $alreadyRegistered->pluck('user.fullname')->implode(', ');
            return back()->withInput()->with('error', "Peserta berikut sudah terdaftar di event ini: {$names}");
        }

        $totalParticipants = count($validated['participants']);

        if ($event->quota) {
            $registeredCount = EventRegistration::where('event_id', $event->id)
                ->whereIn('status', ['pending', 'paid', 'attended'])
                ->count();

            $remainingQuota = $event->quota - $registeredCount;

            if ($totalParticipants > $remainingQuota) {
                return back()->withInput()->with('error', "Kuota tersisa hanya {$remainingQuota} peserta, kamu mencoba mendaftarkan {$totalParticipants} peserta.");
            }
        }

        $pricePerParticipant = $event->event_type === 'free' ? 0 : $event->price;
        $totalAmount = $pricePerParticipant * $totalParticipants;
        $transactionCode = $request->transaction_code;

        if (!$transactionCode || Transaction::where('transaction_code', $transactionCode)->exists()) {
            $transactionCode = $this->generateTransactionCode();
        }
        DB::beginTransaction();

        try {

            // 1. Buat transaksi induk
            $transaction = Transaction::create([
                'id' => (string) Str::uuid(),
                'transaction_code' => $transactionCode,
                'event_id' => $event->id,
                'registered_by' => auth()->id(),
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => $event->event_type === 'free' ? 'paid' : 'pending',
                'paid_at' => $event->event_type === 'free' ? now() : null,
            ]);

            // 2. Buat registrasi per peserta, link ke transaksi
            foreach ($validated['participants'] as $p) {
                $isGuest = empty($p['user_id']);
                EventRegistration::create([
                    'id' => (string) Str::uuid(),
                    'event_id' => $event->id,
                    'user_id' => $isGuest ? null : $p['user_id'],
                    'guest_name' => $isGuest ? $p['name'] : null,
                    'guest_email' => $isGuest ? $p['email'] : null,
                    'registered_by' => auth()->id(),
                    'transaction_id' => $transaction->id,
                    'ticket_code' => $this->generateTicketCode(),
                    'price' => $pricePerParticipant,
                    'status' => $event->event_type === 'free' ? 'paid' : 'pending',
                    'registered_at' => now(),
                ]);
            }

            DB::commit();

            if ($event->event_type === 'free') {
                return redirect()
                    ->route('events.register', $event->id)
                    ->with('success', 'Pendaftaran event berhasil!');
            }

            // Arahkan ke menu Transaksi untuk upload bukti / lanjut bayar
            return redirect()
                ->route('transactions.show', $transaction->id)
                ->with('success', 'Pendaftaran berhasil dibuat. Silakan selesaikan pembayaran.');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal melakukan pendaftaran: ' . $e->getMessage());
        }
    }

    private function generateTransactionCode(): string
    {
        do {
            $code = 'TRX-' . strtoupper(Str::random(10));
        } while (Transaction::where('transaction_code', $code)->exists());

        return $code;
    }

    private function generateTicketCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (EventRegistration::where('ticket_code', $code)->exists());

        return $code;
    }
}