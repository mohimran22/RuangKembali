@extends('tablar::page')
@section('content')
@php
    $rp  = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $pct = fn ($v) => number_format($v, 1, ',', '.') . '%';
    $warnaProgress = fn ($v) => $v >= 100 ? 'good' : ($v >= 70 ? 'mid' : 'bad');
    $warnaTumbuh   = fn ($v) => $v >= 100 ? 'good' : 'bad';
@endphp

<style>
    .df{--ink:#13211F;--muted:#62726F;--line:#DCE4E2;--surface:#fff;--head:#F6F9F8;--brand:#0E5B52;--brand-soft:#D9EEEA;--amber:#D9941A;--good:#117A4D;--mid:#B7791F;--bad:#C2410C;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--ink)}
    .df *{box-sizing:border-box}
    .df-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
    .df-head h1{margin:0;font-size:26px;font-weight:800;letter-spacing:-.02em}
    .df-chip{background:var(--brand-soft);color:var(--brand);font-weight:700;padding:6px 14px;border-radius:999px}
    .df-card{background:var(--surface);border:1px solid var(--line);border-radius:16px;overflow:hidden;min-width:0;max-width:100%;contain:inline-size}
    .df-scroll{overflow-x:auto}
    .df table{border-collapse:separate;border-spacing:0;width:100%;min-width:1500px}
    .df th,.df td{padding:11px 12px;text-align:right;white-space:nowrap;border-bottom:1px solid var(--line);font-variant-numeric:tabular-nums}
    .df th{background:var(--head);font-size:13px;font-weight:700;padding-top:14px;padding-bottom:14px}
    .df th:first-child,.df td:first-child{position:sticky;left:0;z-index:2;text-align:left;background:var(--surface);min-width:150px;border-right:1px solid var(--line)}
    .df th:first-child{background:var(--head)}
    .df .tot{position:sticky;right:0;z-index:2;background:var(--brand-soft);border-left:1px solid var(--line);min-width:150px;font-weight:700}
    .df th.tot{color:var(--brand)}
    .df tr:last-child td{border-bottom:0}
    .df .lbl b{display:block}
    .df .lbl small{color:var(--muted);font-size:12px}
    .df .dash{color:var(--muted)}
    .df .good{color:var(--good)} .df .mid{color:var(--mid)} .df .bad{color:var(--bad)}
    .df .pct{font-weight:700}
    .df .bar{height:5px;border-radius:3px;background:var(--line);margin:5px 0 0 auto;width:110px;overflow:hidden}
    .df .bar i{display:block;height:100%;border-radius:3px}
    .df .bar i.good{background:var(--good)} .df .bar i.mid{background:var(--mid)} .df .bar i.bad{background:var(--bad)}
    .df-sec{margin-top:28px}
    .df-sec h2{font-size:18px;margin:0 0 12px}
    .df-chart{padding:18px;height:360px}
    .df-year{display:flex;align-items:center;gap:6px}
    .df-year select.df-chip{border:0;appearance:none;-webkit-appearance:none;cursor:pointer;padding:6px 16px;text-align:center;outline:0}
    .df-nav{width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:50%;border:1px solid var(--line);background:var(--surface);color:var(--ink);font-size:20px;line-height:1;text-decoration:none}
    .df-nav:hover{background:var(--brand-soft);color:var(--brand)}
    .df-btn{background:var(--brand);color:#fff;border:0;font-weight:700;padding:8px 18px;border-radius:999px;cursor:pointer}
    .df-btn:hover{filter:brightness(1.1)}
    .df-btn.ghost{background:var(--head);color:var(--ink);border:1px solid var(--line)}
    .df-alert{background:var(--brand-soft);color:var(--brand);font-weight:600;padding:10px 16px;border-radius:12px;margin-bottom:14px}
    .df-alert-err{background:#FDE8E0;color:var(--bad)}
    .df-modal .modal-content{border-radius:16px;border:1px solid var(--line)}
    .df-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px}
    .df-grid label{font-size:13px;font-weight:700;margin-bottom:4px;display:block}
    .df-input{display:flex;align-items:center;border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff}
    .df-input span{padding:0 10px;color:var(--muted);background:var(--head);align-self:stretch;display:flex;align-items:center;font-size:13px}
    .df-input input{border:0;outline:0;padding:9px 10px;width:100%;text-align:right;font-variant-numeric:tabular-nums}
    .df-input:focus-within{border-color:var(--brand);box-shadow:0 0 0 3px var(--brand-soft)}
</style>

<div class="df" style="width:100%;min-width:0;margin:0;padding:90px 0 64px">
    <div class="df-head">
        <div>
            <h1>Dashboard Finance</h1>
            <div style="color:var(--muted)">Omset dan target bulanan {{ $year }}</div>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <button type="button" class="df-btn" data-bs-toggle="modal" data-bs-target="#modalTarget">Atur Target</button>
            <div class="df-year">
                <a href="{{ request()->fullUrlWithQuery(['year' => $year - 1]) }}" class="df-nav" title="Tahun sebelumnya">&#8249;</a>
                <select class="df-chip" onchange="location.href=this.value" aria-label="Pilih tahun">
                    @for ($y = now()->year + 2; $y >= now()->year - 5; $y--)
                        <option value="{{ request()->fullUrlWithQuery(['year' => $y]) }}" @selected($y == $year)>{{ $y }}</option>
                    @endfor
                </select>
                <a href="{{ request()->fullUrlWithQuery(['year' => $year + 1]) }}" class="df-nav" title="Tahun berikutnya">&#8250;</a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="df-alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="df-alert df-alert-err">{{ $errors->first() }}</div>
    @endif

    <div class="df-card">
        <div class="df-scroll">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        @foreach ($rows as $r)
                            <th>{{ $r['label'] }}</th>
                        @endforeach
                        <th class="tot">Total {{ $year }}</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Omset tahun lalu --}}
                    <tr>
                        <td class="lbl"><b>Omset {{ $year - 1 }}</b><small>pembanding</small></td>
                        @foreach ($rows as $r)
                            <td>{{ $rp($r['omset_lalu']) }}</td>
                        @endforeach
                        <td class="tot">{{ $rp($total['omset_lalu']) }}</td>
                    </tr>

                    {{-- Target --}}
                    <tr>
                        <td class="lbl"><b>Target</b><small>rencana {{ $year }}</small></td>
                        @foreach ($rows as $r)
                            <td>{{ $rp($r['target']) }}</td>
                        @endforeach
                        <td class="tot">{{ $rp($total['target']) }}</td>
                    </tr>

                    {{-- Omset --}}
                    <tr>
                        <td class="lbl"><b>Omset</b><small>realisasi {{ $year }}</small></td>
                        @foreach ($rows as $r)
                            <td>{!! $r['omset'] === null ? '<span class="dash">–</span>' : $rp($r['omset']) !!}</td>
                        @endforeach
                        <td class="tot">{!! $total['omset'] === null ? '<span class="dash">–</span>' : $rp($total['omset']) !!}</td>
                    </tr>

                    {{-- Progress = omset / target --}}
                    <tr>
                        <td class="lbl"><b>Progress</b><small>omset ÷ target</small></td>
                        @foreach (array_merge($rows, ['total' => $total]) as $key => $r)
                            <td class="{{ $key === 'total' ? 'tot' : '' }}">
                                @if ($r['progress'] === null)
                                    <span class="dash">–</span>
                                @else
                                    @php $w = $warnaProgress($r['progress']); @endphp
                                    <span class="pct {{ $w }}">{{ $pct($r['progress']) }}</span>
                                    <div class="bar"><i class="{{ $w }}" style="width:{{ min($r['progress'], 100) }}%"></i></div>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    {{-- Pertumbuhan = omset / omset tahun lalu --}}
                    <tr>
                        <td class="lbl"><b>Pertumbuhan</b><small>omset ÷ omset {{ $year - 1 }}</small></td>
                        @foreach (array_merge($rows, ['total' => $total]) as $key => $r)
                            <td class="{{ $key === 'total' ? 'tot' : '' }}">
                                @if ($r['pertumbuhan'] === null)
                                    <span class="dash">–</span>
                                @else
                                    <span class="pct {{ $warnaTumbuh($r['pertumbuhan']) }}">{{ $pct($r['pertumbuhan']) }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="df-sec">
        <h2>Kurva</h2>
        <div class="df-card df-chart"><canvas id="kurvaFinance"></canvas></div>
    </div>
</div>

{{-- Modal input target --}}
<div class="modal modal-blur fade df-modal" id="modalTarget" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content df" method="POST" action="{{ route('dashboard-finance.target.store') }}" id="formTarget">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <div class="modal-header">
                <h3 class="modal-title" style="font-weight:800">Target Omset {{ $year }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom:14px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                    <span style="color:var(--muted);font-size:13px">Isi semua bulan sekaligus:</span>
                    <div class="df-input" style="width:190px"><span>Rp</span><input type="text" inputmode="numeric" class="rupiah" id="targetSama" placeholder="0"></div>
                    <button type="button" class="df-btn ghost" id="btnSamakan">Samakan</button>
                    <button type="button" class="df-btn ghost" id="btnBagi">Bagi rata dari total</button>
                </div>
                <div class="df-grid">
                    @foreach ($rows as $i => $r)
                        <div>
                            <label>{{ $r['label'] }}</label>
                            <div class="df-input">
                                <span>Rp</span>
                                <input type="text" inputmode="numeric" class="rupiah target-bulan"
                                       name="targets[{{ $i }}]"
                                       value="{{ $r['target'] ? number_format($r['target'], 0, ',', '.') : '' }}"
                                       placeholder="0">
                            </div>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top:16px;font-weight:700">Total target: <span id="totalTarget" style="color:var(--brand)">Rp 0</span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="df-btn ghost" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="df-btn">Simpan Target</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('js')
<script>
    (() => {
        const fmt = n => n ? Math.round(n).toLocaleString('id-ID') : '';
        const val = el => parseInt((el.value || '').replace(/\D/g, '')) || 0;
        const inputs = [...document.querySelectorAll('.target-bulan')];
        const total = () => {
            document.getElementById('totalTarget').textContent = 'Rp ' + (inputs.reduce((s, e) => s + val(e), 0)).toLocaleString('id-ID');
        };
        document.querySelectorAll('.rupiah').forEach(el => el.addEventListener('input', () => {
            el.value = fmt(val(el)); total();
        }));
        document.getElementById('btnSamakan').onclick = () => {
            const v = val(document.getElementById('targetSama'));
            inputs.forEach(e => e.value = fmt(v)); total();
        };
        document.getElementById('btnBagi').onclick = () => {
            const v = Math.floor(val(document.getElementById('targetSama')) / 12);
            inputs.forEach(e => e.value = fmt(v)); total();
        };
        // kirim angka murni (tanpa titik) ke server
        document.getElementById('formTarget').addEventListener('submit', () => {
            inputs.forEach(e => e.value = val(e));
        });
        total();
    })();
</script>
@endpush
@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels = @json(collect($rows)->pluck('label')->values());
    const jt = a => a.map(v => v === null ? null : v / 1e6);

    new Chart(document.getElementById('kurvaFinance'), {
        data: {
            labels,
            datasets: [
                {type:'bar',  label:'Omset {{ $year }}',  data: jt(@json(collect($rows)->pluck('omset')->values())),
                 backgroundColor:'#544a1e', borderRadius:6, maxBarThickness:34, order:3},
                {type:'line', label:'Target {{ $year }}', data: jt(@json(collect($rows)->pluck('target')->values())),
                 borderColor:'#dccba8', backgroundColor:'#dccba8', cubicInterpolationMode:'monotone', borderWidth:2.5, pointRadius:3, order:1},
                {type:'line', label:'Omset {{ $year - 1 }}', data: jt(@json(collect($rows)->pluck('omset_lalu')->values())),
                 borderColor:'#c3bbb0', borderDash:[6,5], cubicInterpolationMode:'monotone', borderWidth:2, pointRadius:0, order:2},
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            interaction:{mode:'index', intersect:false},
            plugins:{
                legend:{labels:{usePointStyle:true}},
                tooltip:{callbacks:{label:c => `${c.dataset.label}: Rp ${Math.round((c.parsed.y||0)*1e6).toLocaleString('id-ID')}`}}
            },
            scales:{
                x:{grid:{display:false}},
                y:{beginAtZero:true, ticks:{callback:v => v + ' jt'}, title:{display:true, text:'Rupiah (juta)'}}
            }
        }
    });
</script>
@endpush