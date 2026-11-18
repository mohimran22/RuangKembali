<div class="col-12">
    <div class="card shadow-sm border-0 rounded-4" id="trafficCard">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📊 Analisis Traffic Website</h5>
            <select id="trafficRange" class="form-select form-select-sm w-auto">
                <option value="7">7 hari terakhir</option>
                <option value="28">28 hari terakhir</option>
                <option value="90">90 hari terakhir</option>
            </select>
        </div>

        <div class="card-body p-4">
            <div id="trafficLoading" class="text-center text-secondary py-5">Memuat data traffic…</div>
            <div id="trafficError" class="alert alert-warning d-none mb-0"></div>

            <div id="trafficContent" class="d-none">
                <div class="row g-3 mb-4">
                    @foreach ([
                        'visitors' => 'Pengunjung',
                        'views'    => 'Tayangan Halaman',
                        'sessions' => 'Sesi',
                        'duration' => 'Rata-rata Durasi Sesi',
                    ] as $key => $label)
                        <div class="col-6 col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-secondary">{{ $label }}</div>
                                <div class="fs-2 fw-bold" data-kpi="{{ $key }}">–</div>
                                <div class="small" data-kpi-change="{{ $key }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="height:280px"><canvas id="trafficChart"></canvas></div>

                <div class="row g-4 mt-2">
                    <div class="col-md-6">
                        <h6 class="mb-2">Halaman Terpopuler</h6>
                        <table class="table table-sm align-middle mb-0">
                            <tbody id="trafficTopPages"></tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-2">Sumber Pengunjung</h6>
                        <table class="table table-sm align-middle mb-0">
                            <tbody id="trafficReferrers"></tbody>
                        </table>
                    </div>
                </div>

                <div class="small text-secondary mt-3" id="trafficUpdated"></div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function () {
    const url = @json(route('dashboard.traffic'));
    const $ = (id) => document.getElementById(id);
    const fmt = (n) => new Intl.NumberFormat('id-ID').format(n);
    let chart;

    function setChange(key, change) {
        const el = document.querySelector(`[data-kpi-change="${key}"]`);
        if (change === null || change === undefined) { el.textContent = ''; return; }
        const up = change >= 0;
        el.className = 'small ' + (up ? 'text-success' : 'text-danger');
        el.textContent = (up ? '▲ ' : '▼ ') + Math.abs(change) + '% dari periode sebelumnya';
    }

    function fillList(id, rows) {
        const tbody = $(id);
        tbody.innerHTML = '';
        if (!rows.length) {
            tbody.innerHTML = '<tr><td class="text-secondary">Belum ada data</td></tr>';
            return;
        }
        rows.forEach(r => {
            const tr = document.createElement('tr');
            const a = document.createElement('td');
            a.textContent = r.label;           // textContent: aman dari XSS
            a.className = 'text-truncate';
            a.style.maxWidth = '260px';
            const b = document.createElement('td');
            b.className = 'text-end fw-semibold';
            b.textContent = fmt(r.value);
            tr.append(a, b);
            tbody.append(tr);
        });
    }

    function render(d) {
        Object.entries(d.kpi).forEach(([k, v]) => {
            document.querySelector(`[data-kpi="${k}"]`).textContent =
                typeof v.value === 'number' ? fmt(v.value) : v.value;
            setChange(k, v.change);
        });

        if (chart) chart.destroy();
        chart = new Chart($('trafficChart'), {
            type: 'bar',
            data: {
                labels: d.chart.labels,
                datasets: [
                    { label: 'Tayangan', data: d.chart.views, backgroundColor: '#9ec5fe' },
                    { label: 'Pengunjung', data: d.chart.visitors, backgroundColor: '#0a3d75' },
                ],
            },
            options: {
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { position: 'bottom' } },
            },
        });

        fillList('trafficTopPages', d.top_pages);
        fillList('trafficReferrers', d.referrers);
        $('trafficUpdated').textContent = 'Diperbarui: ' + d.updated_at + ' (cache 5 menit)';
    }

    async function load(days) {
        $('trafficLoading').classList.remove('d-none');
        $('trafficContent').classList.add('d-none');
        $('trafficError').classList.add('d-none');
        try {
            const res = await fetch(`${url}?days=${days}`, { headers: { 'Accept': 'application/json' } });
            const d = await res.json();
            if (!res.ok || d.error) throw new Error(d.message || 'Gagal memuat data');
            render(d);
            $('trafficContent').classList.remove('d-none');
        } catch (e) {
            $('trafficError').textContent = e.message;
            $('trafficError').classList.remove('d-none');
        } finally {
            $('trafficLoading').classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        $('trafficRange').addEventListener('change', e => load(e.target.value));
        load(7);
    });
})();
</script>
@endpush