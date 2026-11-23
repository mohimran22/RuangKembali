@php
    $state = $event->registration_state;
    $class = $class ?? 'btn btn-primary';

    $voucher = strtoupper(trim((string) request('voucher')));
    $voucher = preg_match('/^[A-Z0-9\-]{1,50}$/', $voucher) ? $voucher : null;
@endphp

@if($state['open'])

    <a href="{{ route('events.register', ['event' => $event->slug, 'voucher' => $voucher]) }}"
       class="{{ $class }}">
        <i class="ti ti-ticket me-1"></i>
        {{ !empty($alreadyRegistered) ? 'Daftarkan Peserta Lain' : 'Daftar Sekarang' }}
    </a>

    {{-- @if(!empty($alreadyRegistered))
        <div class="small text-success mt-1">
            <i class="ti ti-circle-check me-1"></i>Kamu sudah terdaftar
        </div>
    @endif --}}

@else

    <button type="button" class="{{ $class }} is-closed" disabled aria-disabled="true">
        <i class="ti ti-lock me-1"></i>{{ $state['label'] }}
    </button>

@endif