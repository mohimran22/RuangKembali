<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
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

        return view('events.register', compact(
            'event',
            'registration'
        ));
    }

    public function store(Request $request, Event $event)
    {
        abort_unless($event->is_published, 404);

        if (
            $event->registration_open &&
            now()->lt($event->registration_open)
        ) {
            return back()->with('error', 'Pendaftaran event belum dibuka.');
        }

        if (
            $event->registration_close &&
            now()->gt($event->registration_close)
        ) {
            return back()->with('error', 'Pendaftaran event sudah ditutup.');
        }

        $validated = $request->validate([
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.user_id' => ['required', 'uuid', 'exists:users,id'],
            'payment_method' => [
                $event->event_type !== 'free' ? 'required' : 'nullable',
                'in:transfer,gateway',
            ],
        ]);

        $userIds = array_column($validated['participants'], 'user_id');

        // Cek duplikat user_id dalam satu submission
        if (count($userIds) !== count(array_unique($userIds))) {
            return back()
                ->withInput()
                ->with('error', 'Terdapat peserta yang sama dipilih lebih dari sekali.');
        }

        // Pastikan pendaftar (user yang login) ikut sebagai salah satu peserta
        if (!in_array(auth()->id(), $userIds)) {
            return back()
                ->withInput()
                ->with('error', 'Kamu wajib ikut sebagai salah satu peserta.');
        }

        // Cek apakah ada peserta yang sudah terdaftar sebelumnya di event ini
        $alreadyRegistered = EventRegistration::where('event_id', $event->id)
            ->whereIn('user_id', $userIds)
            ->with('user:id,fullname')
            ->get();

        if ($alreadyRegistered->isNotEmpty()) {
            $names = $alreadyRegistered->pluck('user.fullname')->implode(', ');

            return back()
                ->withInput()
                ->with('error', "Peserta berikut sudah terdaftar di event ini: {$names}");
        }

        // Cek kuota, memperhitungkan jumlah peserta baru yang mau ditambahkan
        if ($event->quota) {

            $registeredCount = EventRegistration::where('event_id', $event->id)
                ->whereIn('status', ['pending', 'paid', 'attended'])
                ->count();

            $remainingQuota = $event->quota - $registeredCount;

            if (count($userIds) > $remainingQuota) {
                return back()
                    ->withInput()
                    ->with('error', "Kuota tersisa hanya {$remainingQuota} peserta, kamu mencoba mendaftarkan " . count($userIds) . " peserta.");
            }
        }

        DB::beginTransaction();

        try {

            $registrations = collect();

            foreach ($userIds as $userId) {

                $registration = EventRegistration::create([
                    'id' => (string) Str::uuid(),
                    'event_id' => $event->id,
                    'user_id' => $userId,
                    'registered_by' => auth()->id(),
                    'ticket_code' => $this->generateTicketCode(),
                    'payment_method' => $request->payment_method,
                    'price' => $event->event_type === 'free' ? 0 : $event->price, // <- snapshot di sini
                    'status' => $event->event_type === 'free' ? 'paid' : 'pending',
                    'registered_at' => now(),
                ]);

                $registrations->push($registration);
            }

            DB::commit();

            return redirect()
                ->route('events.register', $event->id)
                ->with('success', 'Pendaftaran event berhasil!');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal melakukan pendaftaran: ' . $e->getMessage());
        }
    }

    private function generateTicketCode(): string
    {
        do {
            $code = 'TKT-' . strtoupper(Str::random(10));
        } while (
            EventRegistration::where('ticket_code', $code)->exists()
        );

        return $code;
    }
}