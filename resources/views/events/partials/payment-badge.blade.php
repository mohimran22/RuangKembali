@if(in_array($registration->status, ['paid', 'confirmed', 'attended']))
    <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Lunas</span>
@elseif($registration->status === 'pending')
    <span class="badge bg-warning">
        <i class="ti ti-clock me-1"></i>
        {{ $registration->transaction?->status === 'waiting_confirmation' ? 'Menunggu Konfirmasi' : 'Menunggu Pembayaran' }}
    </span>
@elseif($registration->status === 'cancelled')
    <span class="badge bg-danger"><i class="ti ti-circle-x me-1"></i>Dibatalkan</span>
@else
    <span class="badge bg-secondary">{{ ucfirst($registration->status ?? '-') }}</span>
@endif