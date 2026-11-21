<?php

namespace App\Http\Controllers;

use App\Helpers\ActiveRole;
use App\Models\Event;
use App\Models\EventVoucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class VoucherController extends Controller
{
    private function authorizePermission(string $permission): void
    {
        abort_unless(ActiveRole::hasPermission($permission), 403);
    }

    public function index(Request $request)
    {
        $this->authorizePermission('lihat daftar voucher');

        if ($request->ajax()) {
            $query = EventVoucher::with('event:id,name')->latest();

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('event_name', fn ($v) => $v->event
                    ? e($v->event->name)
                    : '<span class="badge bg-blue">Semua Event</span>')
                ->filterColumn('event_name', fn ($q, $kw) => $q->whereHas(
                    'event', fn ($e) => $e->where('name', 'ilike', '%' . addcslashes($kw, '%_\\') . '%')
                ))
                ->addColumn('value_label', fn ($v) => e($v->value_label))
                ->addColumn('quota_label', fn ($v) => $v->used_count . ' / ' . ($v->quota ?? '∞'))
                ->addColumn('validity', fn ($v) => ($v->valid_from?->translatedFormat('d M Y') ?? '-')
                    . ' – ' . ($v->valid_until?->translatedFormat('d M Y') ?? '-'))
                ->addColumn('status_badge', function ($v) {
                    $map = [
                        'active'    => ['Aktif', 'bg-success'],
                        'inactive'  => ['Nonaktif', 'bg-secondary'],
                        'upcoming'  => ['Belum Mulai', 'bg-blue'],
                        'expired'   => ['Kadaluarsa', 'bg-danger'],
                        'exhausted' => ['Kuota Habis', 'bg-warning'],
                    ];
                    [$label, $class] = $map[$v->status_key];

                    return '<span class="badge ' . $class . '">' . $label . '</span>';
                })
                ->addColumn('action', function ($v) {
                    $html = '<div class="btn-list flex-nowrap">';

                    if (ActiveRole::hasPermission('ubah data voucher')) {
                        $html .= '<a href="' . route('vouchers.edit', $v->id) . '" class="btn btn-sm btn-secondary" title="Ubah"><i class="ti ti-edit"></i></a>';
                    }
                    if (ActiveRole::hasPermission('hapus data voucher')) {
                        $html .= '<button type="button" class="btn btn-sm btn-danger btn-delete-voucher" data-id="' . $v->id . '" title="Hapus"><i class="ti ti-trash"></i></button>';
                    }

                    return $html . '</div>';
                })
                ->rawColumns(['event_name', 'status_badge', 'action'])
                ->make(true);
        }

        return view('vouchers.index');
    }

    public function create()
    {
        $this->authorizePermission('tambah data voucher');

        return view('vouchers.create', ['events' => $this->eventOptions()]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('tambah data voucher');
        $this->normalize($request);

        $data = $this->payload($request->validate($this->rules($request), $this->messages()));

        DB::transaction(function () use ($data) {
            $data['voucher_number'] = EventVoucher::generateNumber();
            EventVoucher::create($data);
        });

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(EventVoucher $voucher)
    {
        $this->authorizePermission('ubah data voucher');

        return view('vouchers.edit', [
            'voucher' => $voucher,
            'events'  => $this->eventOptions(),
            'locked'  => $voucher->hasBeenUsed(),
        ]);
    }

    public function update(Request $request, EventVoucher $voucher)
    {
        $this->authorizePermission('ubah data voucher');
        $this->normalize($request);

        $locked = $voucher->hasBeenUsed();

        $data = $request->validate($this->rules($request, $voucher, $locked), $this->messages());

        $voucher->update($this->payload($data, $voucher));

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(EventVoucher $voucher)
    {
        $this->authorizePermission('hapus data voucher');

        if ($voucher->hasBeenUsed()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Voucher ini sudah pernah dipakai transaksi, jadi tidak bisa dihapus. Nonaktifkan saja.',
            ], 422);
        }

        $voucher->delete();

        return response()->json(['status' => 'success', 'message' => 'Voucher berhasil dihapus.']);
    }

    // ------------------------------------------------------------------

    private function eventOptions()
    {
        return Event::orderByDesc('start_at')->get(['id', 'name', 'start_at']);
    }

    private function normalize(Request $request): void
    {
        $request->merge([
            'code'      => strtoupper(trim((string) $request->input('code'))),
            'is_active' => $request->boolean('is_active'),
        ]);
    }

    private function rules(Request $request, ?EventVoucher $voucher = null, bool $locked = false): array
    {
        $isPercent = $request->input('type') === 'percent';
        $minQuota  = $voucher ? max(1, $voucher->used_count) : 1;

        $rules = [
            'code' => [
                'required', 'string', 'max:30', 'regex:/^[A-Z0-9\-]+$/',
                Rule::unique('event_vouchers', 'code')->ignore($voucher?->id),
            ],
            'name'         => ['required', 'string', 'max:150'],
            'event_id'     => ['nullable', 'uuid', 'exists:events,id'],
            'type'         => ['required', Rule::in(['percent', 'fixed'])],
            'value'        => ['required', 'numeric', $isPercent ? 'between:1,100' : 'min:1', 'max:999999999'],
            'max_discount' => ['nullable', 'numeric', 'min:1', 'max:999999999'],
            'quota'        => ['nullable', 'integer', 'min:' . $minQuota, 'max:1000000'],
            'valid_from'   => ['required', 'date_format:Y-m-d'],
            'valid_until'  => ['required', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
            'is_active'    => ['boolean'],
        ];

        // Voucher yang sudah dipakai: kode, jenis, nominal, dan event dikunci
        if ($locked) {
            unset($rules['code'], $rules['type'], $rules['value'], $rules['event_id']);
        }

        return $rules;
    }

    private function messages(): array
    {
        return [
            'code.regex'              => 'Kode voucher hanya boleh berisi huruf, angka, dan tanda hubung.',
            'code.unique'             => 'Kode voucher ini sudah dipakai.',
            'value.between'           => 'Nominal persen harus antara 1 sampai 100.',
            'value.min'               => 'Nominal rupiah minimal 1.',
            'quota.min'               => 'Kuota tidak boleh lebih kecil dari jumlah yang sudah terpakai (minimal :min).',
            'valid_until.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }

    private function payload(array $data, ?EventVoucher $voucher = null): array
    {
        $data['valid_from']  = Carbon::createFromFormat('Y-m-d', $data['valid_from'])->startOfDay();
        $data['valid_until'] = Carbon::createFromFormat('Y-m-d', $data['valid_until'])->endOfDay();

        if (array_key_exists('event_id', $data)) {
            $data['event_id'] = $data['event_id'] ?: null;
        }

        $type = $data['type'] ?? $voucher?->type;

        // Batas maksimal hanya masuk akal untuk potongan persen
        if ($type !== 'percent') {
            $data['max_discount'] = null;
        }

        return $data;
    }
}