@extends('tablar::page')
@section('content')
@php
    $rp  = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $rpS = fn ($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');
    $pct = fn ($v) => number_format($v, 1, ',', '.') . '%';
    $warnaProgress = fn ($v) => $v >= 100 ? 'good' : ($v >= 70 ? 'mid' : 'bad');
    $warnaTumbuh   = fn ($v) => $v >= 100 ? 'good' : 'bad';
@endphp

<style>
    .df{--ink:#13211F;--muted:#62726F;--line:#DCE4E2;--surface:#fff;--head:#F6F9F8;--brand:#000;--brand-soft:#D9EEEA;--amber:#D9941A;--good:#544a1e;--mid:#B7791F;--bad:#C2410C;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--ink)}
    .df *{box-sizing:border-box}
    .df-head{display:flex;justify-content:space-between;align-items:center;margin:0px 18px 24px}
    .df-head h1{margin:0;font-size:26px;font-weight:800;letter-spacing:-.02em}
    .df-chip{background:var(--brand-soft);color:var(--brand);font-weight:700;padding:6px 14px;border-radius:999px}
    .df-card{background:var(--surface);border:1px solid var(--line);border-radius:16px;overflow:hidden;min-width:0;max-width:100%;contain:inline-size;;margin:0px 18px 24px}
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
    .df-ctrl{display:flex;gap:10px;align-items:center}
    .df-btn{white-space:nowrap}
    .df-year{display:flex;align-items:center;gap:6px}
    .df-year select.df-chip{border:0;appearance:none;-webkit-appearance:none;cursor:pointer;padding:6px 16px;text-align:center;outline:0}
    .df-nav{width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:50%;border:1px solid var(--line);background:var(--surface);color:var(--ink);font-size:20px;line-height:1;text-decoration:none}
    .df-nav:hover{background:var(--brand-soft);color:var(--brand)}
    .df-btn{background:var(--brand);color:#fff;border:0;font-weight:700;padding:8px 18px;border-radius:999px;cursor:pointer}
    .df-btn:hover{filter:brightness(1.1)}
    .df-btn.ghost{background:var(--head);color:var(--ink);border:1px solid var(--line)}
    .df-alert{background:var(--brand-soft);color:var(--brand);font-weight:600;padding:10px 16px;border-radius:12px;margin:14px 18px}
    .df-alert-err{background:#FDE8E0;color:var(--bad)}
    .df-modal .modal-content{border-radius:16px;border:1px solid var(--line)}
    .df-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px}
    .df-grid label{font-size:13px;font-weight:700;margin-bottom:4px;display:block}
    .df-input{display:flex;align-items:center;border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff}
    .df-input span{padding:0 10px;color:var(--muted);background:var(--head);align-self:stretch;display:flex;align-items:center;font-size:13px}
    .df-input input{border:0;outline:0;padding:9px 10px;width:100%;text-align:right;font-variant-numeric:tabular-nums}
    .df-input:focus-within{border-color:var(--brand);box-shadow:0 0 0 3px var(--brand-soft)}
    .df-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:0 18px 24px}

@media (max-width: 991.98px) {
    .df-head {
        margin-left: 14px;
        margin-right: 14px;
    }

    .df-card {
        margin-left: 14px;
        margin-right: 14px;
    }
}

@media (max-width: 575.98px) {

    /* Header: judul 1 baris, kontrol turun ke bawah */
    .df-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .df-head h1 {
        font-size: 22px;
        white-space: nowrap;
    }
    .df-ctrl {
        width: 100%;
        justify-content: space-between;
    }

    .df table {
        min-width: 1300px;           /* 12 bulan x ~95px + label + total */
    }

    .df th,
    .df td {
        padding: 10px 8px;
        font-size: 12px;
    }

    /* Kolom bulan: kasih lebar minimum supaya angka tidak terpotong */
    .df th:not(:first-child):not(.tot),
    .df td:not(:first-child):not(.tot) {
        min-width: 100px;
    }

    /* Kolom label: lebih ramping, tetap sticky */
    .df th:first-child,
    .df td:first-child {
        min-width: 92px;
        max-width: 92px;
        white-space: normal;         /* label boleh turun baris */
        padding-left: 8px;
        padding-right: 6px;
    }

    /* Kolom Total: TIDAK sticky di mobile, ikut digeser */
    .df .tot {
        position: static;
        min-width: 120px;
        border-left: 1px solid var(--line);
    }

    .df .bar {
        width: 80px;
    }
}

</style>

<div class="df" style="width:100%;min-width:0;margin:0;padding:90px 0 64px">
    <div class="df-head">
        <div>
            <h1>Dashboard Finance</h1>
            {{-- <div style="color:var(--muted)">Omset dan target bulanan {{ $year }}</div> --}}
        </div>
        <div class="df-ctrl">
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

    @include('dashboard.finance_table', [
        'judul' => 'Omset', 'rows' => $tabel['omset']['rows'], 'total' => $tabel['omset']['total'],
    ])

    <div class="df-sec">
        <h2 class="page-title">Kurva</h2>
        <div class="df-card df-chart"><canvas id="kurvaFinance"></canvas></div>
    </div>

    <div class="df-sec">
        <h2 class="page-title">Cost</h2>
        @include('dashboard.finance_table', [
            'judul' => 'Cost', 'rows' => $tabel['cost']['rows'], 'total' => $tabel['cost']['total'], 'kurang' => true,
        ])
    </div>

    <div class="df-sec">
        <h2 class="page-title">Profit</h2>
        @include('dashboard.finance_table', [
            'judul' => 'Profit', 'rows' => $tabel['profit']['rows'], 'total' => $tabel['profit']['total'],
        ])
    </div>
</div>

@include('dashboard.finance_modal_target')
@endsection
@push('js')

{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script> --}}
<script>
    const labels = @json(collect($tabel['omset']['rows'])->pluck('label')->values());
    const jt = a => a.map(v => v === null ? null : v / 1e6);
    const labelOmset = {
        id: 'labelOmset',
        afterDatasetsDraw(chart) {
            const {ctx} = chart;
            const mobile = window.innerWidth < 576;

            chart.data.datasets.forEach((ds, i) => {
                if (ds.type !== 'bar') return;

                chart.getDatasetMeta(i).data.forEach((bar, idx) => {
                    const v = ds.data[idx];
                    if (v === null || v === undefined || v <= 0) return;

                    const {x, y, base} = bar.getProps(['x', 'y', 'base'], true);
                    const tinggi = Math.abs(base - y);
                    const txt = Math.round(v).toLocaleString('id-ID') + (mobile ? '' : ' jt');

                    ctx.save();
                    ctx.font = '600 ' + (mobile ? 9 : 11) + 'px system-ui, sans-serif';
                    ctx.textBaseline = 'middle';

                    const muat = ctx.measureText(txt).width + 12 <= tinggi;
                    ctx.translate(x, muat ? y + 6 : y - 4);
                    ctx.rotate(-Math.PI / 2);

                    if (muat) {            // batang cukup tinggi: tulis di dalam (putih)
                        ctx.textAlign = 'right';
                        ctx.fillStyle = '#fff';
                    } else {               // batang pendek: tulis di atas batang (hitam)
                        ctx.textAlign = 'left';
                        ctx.fillStyle = '#000';
                    }
                    ctx.fillText(txt, 0, 0);
                    ctx.restore();
                });
            });
        }
    };
    @php
        $o = $tabel['omset']['rows'];
        $c = $tabel['cost']['rows'];
    @endphp
    new Chart(document.getElementById('kurvaFinance'), {
        data: {
            labels,
            datasets: [
                {type:'bar',  label:'Omset {{ $year }}', data: jt(@json(collect($o)->pluck('real')->values())),
                backgroundColor:'#dccba8', hoverBackgroundColor:'#333', borderRadius:6, maxBarThickness:34, order:3},

                {type:'line', label:'Target {{ $year }}', data: jt(@json(collect($o)->pluck('target')->values())),
                borderColor:'#c3bbb0', backgroundColor:'#c3bbb0', cubicInterpolationMode:'monotone', borderWidth:2.5,
                pointRadius:3, pointHoverRadius:6, pointHitRadius:16, order:1},

                {type:'line', label:'Omset {{ $year - 1 }}', data: jt(@json(collect($o)->pluck('lalu')->values())),
                borderColor:'#6d4e35', borderDash:[6,5], cubicInterpolationMode:'monotone', borderWidth:2,
                pointRadius:2, pointHoverRadius:6, pointHitRadius:16, order:2},

                {type:'line', label:'Cost {{ $year }}', data: jt(@json(collect($c)->pluck('real')->values())),
                borderColor:'#C2410C', backgroundColor:'#C2410C', cubicInterpolationMode:'monotone', borderWidth:2.5,
                pointRadius:3, pointHoverRadius:6, pointHitRadius:16, order:0},
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            events:['click','touchstart','touchmove'],         // respons ke sentuhan
            interaction:{mode:'nearest', intersect:true},      // hanya elemen yang disentuh
            plugins:{
                legend:{labels:{usePointStyle:true}},
                tooltip:{
                    displayColors:true,
                    callbacks:{
                        label:c => `${c.dataset.label}: Rp ${Math.round((c.parsed.y||0)*1e6).toLocaleString('id-ID')}`
                    }
                }
            },
            scales:{
                x:{grid:{display:false}},
                y:{beginAtZero:true, ticks:{callback:v => v + ' jt'}, title:{display:true, text:'Rupiah (juta)'}}
            }
        }
    });
</script>
@endpush