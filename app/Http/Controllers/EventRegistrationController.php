<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Transaction;
use App\Helpers\ActiveRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\EventRegistrationSuccessNotification;

class EventRegistrationController extends Controller
{
    public function create(Event $event)
    {
        abort_unless($event->is_published, 404);
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->first();
        $transactionCode = $this->generateTransactionCode();
        $needsProfile = !$registration && ActiveRole::name() !== 'Super-Admin';
        $missingProfile = $needsProfile ? auth()->user()->missingProfileFields() : [];

        if ($missingProfile) {
            session(['url.intended' => url()->current()]);
        }
        $canGiveComplimentary = auth()->user()->hasAnyRole(['Super-Admin', 'NAMA-ROLE-PANITIA']);
        return view('events.register', compact(
            'event',
            'registration',
            'transactionCode',
            'missingProfile',
            'canGiveComplimentary'
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
            'participants.*.gender' => ['nullable', 'in:1,2'],
            'payment_method' => [
                $event->event_type !== 'free' ? 'required' : 'nullable',
                'in:transfer,gateway',
            ],
            'transaction_code' => ['nullable', 'string'],
        ]);

        foreach ($validated['participants'] as $p) {
            $hasUserId = !empty($p['user_id']);
            $hasGuestInfo = !empty($p['name']) && !empty($p['email']) && !empty($p['gender']);

            if (!$hasUserId && !$hasGuestInfo) {
                return back()->withInput()->with('error', 'Setiap peserta harus berupa user terdaftar, atau diisi nama, email, dan jenis kelamin.');
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

        $iAmRegistered = EventRegistration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'cancelled')
            ->exists();

        if (ActiveRole::name() !== 'Super-Admin'
            && !$iAmRegistered
            && !in_array(auth()->id(), $userIds)) {
            return back()->withInput()->with('error', 'Kamu wajib ikut sebagai salah satu peserta.');
        }
        // (c) tepat setelah pengecekan "Kamu wajib ikut", sebelum DB::beginTransaction()
        if (!empty($userIds)) {
            $incomplete = User::whereIn('id', $userIds)->get()
                ->reject(fn ($u) => $u->hasCompleteProfile());

            // Data pendaftar sendiri kurang: arahkan ke profil, lalu kembali ke sini
            if ($incomplete->contains('id', auth()->id())) {
                session(['url.intended' => route('events.register', $event->slug)]);

                return redirect()->route('profile.edit')->with(
                    'error',
                    'Lengkapi data dirimu dulu: ' . implode(', ', auth()->user()->missingProfileFields()) . '.'
                );
            }

            // Data peserta lain kurang: tolak dan sebutkan siapa dan apa yang kurang
            if ($incomplete->isNotEmpty()) {
                $detail = $incomplete
                    ->map(fn ($u) => $u->fullname . ' (belum mengisi: ' . implode(', ', $u->missingProfileFields()) . ')')
                    ->implode('; ');

                return back()->withInput()->with(
                    'error',
                    "Data diri peserta berikut belum lengkap: {$detail}. Minta yang bersangkutan melengkapi profilnya terlebih dahulu."
                );
            }
        }
        $totalParticipants = count($validated['participants']);
        $pricePerParticipant = $event->event_type === 'free' ? 0 : $event->price;
        $totalAmount = $pricePerParticipant * $totalParticipants;
        $transactionCode = $request->transaction_code;

        if (!$transactionCode || Transaction::where('transaction_code', $transactionCode)->exists()) {
            $transactionCode = $this->generateTransactionCode();
        }
        DB::beginTransaction();

        try {
            Event::whereKey($event->id)->lockForUpdate()->first();

            $alreadyRegistered = EventRegistration::where('event_id', $event->id)
                ->whereIn('user_id', $userIds)
                ->where('status', '!=', 'cancelled')
                ->with('user:id,fullname')
                ->get();

            if ($alreadyRegistered->isNotEmpty()) {
                DB::rollBack();
                $names = $alreadyRegistered->pluck('user.fullname')->implode(', ');
                return back()->withInput()->with('error', "Peserta berikut sudah terdaftar di event ini: {$names}");
            }
            $guestEmailsExisting = EventRegistration::where('event_id', $event->id)
                ->whereIn(DB::raw('LOWER(guest_email)'), $guestEmails)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($guestEmailsExisting) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Ada email peserta manual yang sudah terdaftar di event ini.');
            }
            if ($event->quota) {
                $registeredCount = EventRegistration::where('event_id', $event->id)
                    ->whereIn('status', ['pending', 'paid', 'attended'])
                    ->count();

                $remainingQuota = $event->quota - $registeredCount;

                if ($totalParticipants > $remainingQuota) {
                    DB::rollBack();
                    return back()->withInput()->with('error', "Kuota tersisa hanya {$remainingQuota} peserta, kamu mencoba mendaftarkan {$totalParticipants} peserta.");
                }
            }
            // 1. Buat transaksi induk
            $transaction = Transaction::create([
                'id' => (string) Str::uuid(),
                'transaction_code' => $transactionCode,
                'event_id' => $event->id,
                'registered_by' => auth()->id(),
                'total_amount' => $totalAmount,
                'expires_at' => $event->event_type === 'free' ? null : now()->addDay(),
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
                    'guest_gender' => $isGuest ? (int) $p['gender'] : null,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->with('error', 'Pendaftaran gagal diproses. Silakan coba lagi.');
        }
            try {
                $transaction->load(['event', 'registrations.user', 'registeredBy']);

                if ($transaction->registeredBy) {
                    $transaction->registeredBy->notify(
                        new EventRegistrationSuccessNotification($transaction)
                    );
                }
            } catch (\Throwable $mailError) {
                report($mailError);
            }
            if ($event->event_type === 'free') {
                return redirect()
                    ->route('events.register', $event->slug)
                    ->with('success', 'Pendaftaran event berhasil!');
            }

            // Arahkan ke menu Transaksi untuk upload bukti / lanjut bayar
            return redirect()
                ->route('transactions.show', $transaction->id)
                ->with('success', 'Pendaftaran berhasil dibuat. Silakan selesaikan pembayaran.');
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