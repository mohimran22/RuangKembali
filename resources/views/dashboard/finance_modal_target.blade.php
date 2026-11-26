{{-- Modal input target: tab Omset / Cost / Profit. Parameter: $year, $tabel --}}
@php $tipeList = ['omset' => 'Omset', 'cost' => 'Cost', 'profit' => 'Profit']; @endphp

<div class="modal modal-blur fade df-modal" id="modalTarget" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content df" method="POST" action="{{ route('dashboard-finance.target.store') }}" id="formTarget">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <div class="modal-header">
                <h3 class="modal-title" style="font-weight:800">Target {{ $year }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist" style="margin-bottom:16px">
                    @foreach ($tipeList as $k => $nama)
                        <li class="nav-item">
                            <a href="#tab-target-{{ $k }}" class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" role="tab">{{ $nama }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    @foreach ($tipeList as $k => $nama)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-target-{{ $k }}">
                            <div style="margin-bottom:14px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                                <span style="color:var(--muted);font-size:13px">Isi semua bulan sekaligus:</span>
                                <div class="df-input" style="width:190px"><span>Rp</span><input type="text" inputmode="numeric" class="rupiah target-sama" placeholder="0"></div>
                                <button type="button" class="df-btn ghost btn-samakan">Samakan</button>
                                <button type="button" class="df-btn ghost btn-bagi">Bagi rata dari total</button>
                            </div>

                            <div class="df-grid">
                                @foreach ($tabel[$k]['rows'] as $i => $r)
                                    <div>
                                        <label>{{ $r['label'] }}</label>
                                        <div class="df-input">
                                            <span>Rp</span>
                                            <input type="text" inputmode="numeric" class="rupiah target-bulan"
                                                   name="targets[{{ $k }}][{{ $i }}]"
                                                   value="{{ $r['target'] ? number_format($r['target'], 0, ',', '.') : '' }}"
                                                   placeholder="0">
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div style="margin-top:16px;font-weight:700">Total target {{ $nama }}: <span class="total-target" style="color:var(--brand)">Rp 0</span></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="df-btn ghost" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="df-btn">Simpan Target</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    (() => {
        const fmt = n => n ? Math.round(n).toLocaleString('id-ID') : '';
        const val = el => parseInt((el.value || '').replace(/\D/g, '')) || 0;
        const form = document.getElementById('formTarget');

        // tiap tab (Omset / Cost / Profit) punya input, tombol, dan total sendiri
        form.querySelectorAll('.tab-pane').forEach(pane => {
            const inputs  = [...pane.querySelectorAll('.target-bulan')];
            const sama    = pane.querySelector('.target-sama');
            const totalEl = pane.querySelector('.total-target');
            const total   = () => {
                totalEl.textContent = 'Rp ' + inputs.reduce((s, e) => s + val(e), 0).toLocaleString('id-ID');
            };

            pane.querySelectorAll('.rupiah').forEach(el => el.addEventListener('input', () => {
                el.value = fmt(val(el)); total();
            }));
            pane.querySelector('.btn-samakan').onclick = () => {
                const v = val(sama);
                inputs.forEach(e => e.value = fmt(v)); total();
            };
            pane.querySelector('.btn-bagi').onclick = () => {
                const v = Math.floor(val(sama) / 12);
                inputs.forEach(e => e.value = fmt(v)); total();
            };
            total();
        });

        // kirim angka murni (tanpa titik) ke server
        form.addEventListener('submit', () => {
            form.querySelectorAll('.target-bulan').forEach(e => e.value = val(e));
        });
    })();
</script>
@endpush