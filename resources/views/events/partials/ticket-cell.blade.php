<div class="d-flex align-items-center gap-2">
    <span>{{ $registration->ticket_code }}</span>

    @if(in_array($registration->status, ['paid', 'confirmed', 'attended']))
        @if($canViewAll)
            <button type="button" class="btn btn-icon btn-sm btn-outline-secondary btn-copy-ticket"
                    data-url="{{ $ticketUrl }}" title="Salin link tiket">
                <i class="ti ti-link"></i>
            </button>
            <button type="button" class="btn btn-icon btn-sm btn-outline-success btn-wa-ticket"
                    data-url="{{ $ticketUrl }}"
                    data-name="{{ $registration->participant_name }}"
                    data-event="{{ $event->name }}" title="Kirim via WhatsApp">
                <i class="ti ti-brand-whatsapp"></i>
            </button>
        @else
            <button type="button" class="btn btn-icon btn-sm btn-outline-secondary"
                    data-bs-toggle="modal" data-bs-target="#ticketQrModal"
                    data-ticket-code="{{ $registration->ticket_code }}"
                    data-name="{{ $registration->participant_name }}" title="Tampilkan QR Code">
                <i class="ti ti-qrcode"></i>
            </button>
        @endif
    @endif
</div>