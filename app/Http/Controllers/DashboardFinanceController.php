<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardFinanceController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        $omsetLalu = $this->omsetPerBulan($year - 1); // Omset 2026 (pembanding)
        $omsetIni  = $this->omsetPerBulan($year);     // Omset 2027 (realisasi)
        $targets   = DB::table('finance_targets')
            ->where('year', $year)
            ->pluck('target', 'month');

        $now  = now();
        $rows = [];

        for ($m = 1; $m <= 12; $m++) {
            $masaDepan = Carbon::create($year, $m, 1)->startOfMonth()->gt($now);

            $o26    = (float) ($omsetLalu[$m] ?? 0);
            $target = (float) ($targets[$m] ?? 0);
            $omset  = $masaDepan ? null : (float) ($omsetIni[$m] ?? 0);

            $rows[$m] = $this->hitung($o26, $target, $omset);
            $rows[$m]['label'] = Carbon::create($year, $m, 1)->translatedFormat('M Y');
        }

        // Kolom total tahun
        $terisi     = collect($rows)->whereNotNull('omset');
        $totalOmset = $terisi->isEmpty() ? null : $terisi->sum('omset');
        $total = $this->hitung(
            collect($rows)->sum('omset_lalu'),
            collect($rows)->sum('target'),
            $totalOmset
        );

        return view('dashboard.finance', [
            'year'  => $year,
            'rows'  => $rows,
            'total' => $total,
        ]);
    }

    /**
     * Rumus sesuai sketsa:
     *  Progress    = omset / target * 100
     *  Pertumbuhan = omset / omset tahun lalu * 100
     */
    private function hitung(float $omsetLalu, float $target, ?float $omset): array
    {
        return [
            'omset_lalu' => $omsetLalu,
            'target'     => $target,
            'omset'      => $omset,
            'progress'   => ($omset !== null && $target > 0) ? $omset / $target * 100 : null,
            'pertumbuhan'=> ($omset !== null && $omsetLalu > 0) ? $omset / $omsetLalu * 100 : null,
        ];
    }

    /**
     * Omset per bulan (key 1..12) dari jurnal akuntansi:
     * akun Pendapatan = account_code diawali "4", nilai = kredit - debit.
     */
    private function omsetPerBulan(int $year)
    {
        return DB::table('accounting_journal_details as d')
            ->join('accounting_journals as j', 'j.id', '=', 'd.journal_id')
            ->join('accounting_accounts as a', 'a.id', '=', 'd.account_id')
            ->where('a.account_code', 'like', '4%')
            ->whereYear('j.transaction_date', $year)
            ->selectRaw('EXTRACT(MONTH FROM j.transaction_date)::int as bulan, SUM(d.credit - d.debit) as total')
            ->groupByRaw('EXTRACT(MONTH FROM j.transaction_date)')
            ->pluck('total', 'bulan')
            ->map(fn ($v) => (float) $v);
    }
}