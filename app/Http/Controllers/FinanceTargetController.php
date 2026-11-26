<?php

namespace App\Http\Controllers;

use App\Models\FinanceTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceTargetController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'year'        => ['required', 'integer', 'between:2000,2100'],
            'targets'     => ['required', 'array'],
            'targets.*'   => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            foreach (range(1, 12) as $month) {
                FinanceTarget::updateOrCreate(
                    ['year' => $data['year'], 'month' => $month],
                    ['target' => $data['targets'][$month] ?? 0]
                );
            }
        });

        return redirect()
            ->route('dashboard-finance') // sesuaikan dengan nama route dashboard-mu
            ->with('success', 'Target ' . $data['year'] . ' berhasil disimpan.');
    }
}