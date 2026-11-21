@php
    $type = old('type', $voucher->type ?? 'percent');
@endphp

<div class="card">
    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($locked)
            <div class="alert alert-warning">
                <i class="ti ti-lock me-1"></i>
                Voucher ini sudah pernah dipakai transaksi. Kode, jenis nilai, nominal, dan event dikunci.
            </div>
        @endif

        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label">Id Voucher</label>
                <input type="text" class="form-control" readonly
                       value="{{ $voucher->voucher_number ?? 'Dibuat otomatis (VCH-xxxx)' }}">
            </div>

            <div class="col-md-4">
                <label class="form-label required">Kode Voucher</label>
                <div class="input-group">
                    <input type="text" name="code" id="voucherCode" maxlength="30"
                           class="form-control text-uppercase" required @disabled($locked)
                           value="{{ old('code', $voucher->code ?? '') }}" placeholder="Contoh: HEMAT50">
                    @unless($locked)
                        <button type="button" class="btn btn-outline-secondary" id="genCodeBtn">Buat Otomatis</button>
                    @endunless
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label required">Nama Voucher</label>
                <input type="text" name="name" class="form-control" maxlength="150" required
                       value="{{ old('name', $voucher->name ?? '') }}" placeholder="Contoh: Diskon Early Bird">
            </div>

            <div class="col-md-6">
                <label class="form-label">Berlaku untuk Event</label>
                <select name="event_id" class="form-select" @disabled($locked)>
                    <option value="">Semua Event</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}"
                            @selected(old('event_id', $voucher->event_id ?? '') === $event->id)>
                            {{ $event->name }} ({{ $event->start_at?->translatedFormat('d M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Kuota</label>
                <input type="number" name="quota" class="form-control" min="1"
                       value="{{ old('quota', $voucher->quota ?? '') }}" placeholder="Kosongkan untuk tanpa batas">
                <div class="form-hint">
                    Jumlah transaksi yang boleh memakai voucher ini.
                    @if($voucher) Sudah terpakai: {{ $voucher->used_count }}. @endif
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label required">Berlaku Dari</label>
                <input type="date" name="valid_from" class="form-control" required
                       value="{{ old('valid_from', $voucher?->valid_from?->format('Y-m-d')) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label required">Berlaku Sampai</label>
                <input type="date" name="valid_until" class="form-control" required
                       value="{{ old('valid_until', $voucher?->valid_until?->format('Y-m-d')) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label required">Jenis Nilai</label>
                <select name="type" id="voucherType" class="form-select" required @disabled($locked)>
                    <option value="percent" @selected($type === 'percent')>Persen (%)</option>
                    <option value="fixed" @selected($type === 'fixed')>Rupiah (Rp)</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label required">Nominal</label>
                <div class="input-group">
                    <span class="input-group-text" id="valuePrefix">Rp</span>
                    <input type="number" name="value" id="voucherValue" class="form-control"
                           min="1" step="1" required @disabled($locked)
                           value="{{ old('value', isset($voucher) ? (float) $voucher->value : '') }}">
                    <span class="input-group-text" id="valueSuffix">%</span>
                </div>
                <div class="form-hint" id="valueHint"></div>
            </div>

            <div class="col-md-4" id="maxDiscountBox">
                <label class="form-label">Maksimal Potongan per Tiket (opsional)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="max_discount" class="form-control" min="1"
                           value="{{ old('max_discount', isset($voucher) && $voucher->max_discount ? (float) $voucher->max_discount : '') }}">
                </div>
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <label class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                           @checked(old('is_active', $voucher->is_active ?? true))>
                    <span class="form-check-label">Voucher aktif</span>
                </label>
            </div>

        </div>
    </div>

    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy me-1"></i> Simpan
        </button>
    </div>
</div>

@push('js')
<script>
(function () {
    const typeSelect = document.getElementById('voucherType');
    const valueInput = document.getElementById('voucherValue');
    const prefix     = document.getElementById('valuePrefix');
    const suffix     = document.getElementById('valueSuffix');
    const hint       = document.getElementById('valueHint');
    const maxBox     = document.getElementById('maxDiscountBox');

    function sync(clear) {
        const percent = typeSelect.value === 'percent';

        prefix.classList.toggle('d-none', percent);
        suffix.classList.toggle('d-none', !percent);
        maxBox.classList.toggle('d-none', !percent);

        valueInput.min = 1;
        valueInput.step = percent ? '0.01' : '1';
        percent ? valueInput.setAttribute('max', 100) : valueInput.removeAttribute('max');
        valueInput.placeholder = percent ? '1 - 100' : 'Contoh: 50000';
        hint.textContent = percent ? 'Isi angka 1 sampai 100.' : 'Isi jumlah rupiah, misalnya 50000.';

        if (clear) valueInput.value = '';
    }

    typeSelect.addEventListener('change', () => sync(true));
    sync(false);

    const genBtn = document.getElementById('genCodeBtn');
    if (genBtn) {
        genBtn.addEventListener('click', function () {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let code = '';
            for (let i = 0; i < 8; i++) code += chars[Math.floor(Math.random() * chars.length)];
            document.getElementById('voucherCode').value = code;
        });
    }
})();
</script>
@endpush