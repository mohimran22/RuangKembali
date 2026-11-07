<?php

namespace App\Http\Controllers;

use App\Models\AccountingAccount;
use App\Models\AccountingJournalDetail;
use App\Helpers\ActiveRole;
use App\Models\Religion;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use App\Models\User;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\EventRegistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class DashboardController extends Controller
{
    private const COUNTED_STATUS = ['paid', 'attended'];

    public function index()
    {
        $user = auth()->user();

        if (ActiveRole::name() === 'Member') {
            return $this->memberDashboard($user);
        }
        $events = Event::query()
            ->withCount([
                'registrations as registrations_count' => fn ($q) => $q->whereIn('status', self::COUNTED_STATUS),
                'registrations as pending_count'       => fn ($q) => $q->where('status', 'pending'),
                'registrations as checked_in_count'    => fn ($q) => $q->whereNotNull('checked_in_at'),
            ])
            ->orderByDesc('start_at')
            ->take(6)
            ->get();

        $totalEvent        = Event::count();
        $upcomingEvent     = Event::where('start_at', '>=', now())->count();
        $totalRegistration = EventRegistration::whereIn('status', self::COUNTED_STATUS)->count();
        $totalPending      = EventRegistration::where('status', 'pending')->count();
        $totalCheckedIn    = EventRegistration::whereNotNull('checked_in_at')->count();

        // ================= FINANCE =================
        $awalBulan  = now()->startOfMonth();
        $akhirBulan = now()->endOfMonth();

        $groupedAccounts = $this->buildGroupedAccounts(function ($q) {
            $q->whereDate('transaction_date', '<=', today());
        });

        $cashAccounts  = collect(data_get($groupedAccounts, 'AKTIVA.Aset Lancar - Kas & Bank.accounts', []));
        $totalCashBank = $cashAccounts->sum('balance');

        $cashAccountIds = AccountingAccount::where('is_parent', false)
            ->where('category', 'AKTIVA')
            ->where('sub_category', 'Aset Lancar - Kas & Bank')
            ->pluck('id');

        $thisMonth = function ($q) {
            $q->whereMonth('transaction_date', now()->month)
              ->whereYear('transaction_date', now()->year);
        };

        $cashInThisMonth = AccountingJournalDetail::whereIn('account_id', $cashAccountIds)
            ->whereHas('journal', $thisMonth)->sum('debit');

        $cashOutThisMonth = AccountingJournalDetail::whereIn('account_id', $cashAccountIds)
            ->whereHas('journal', $thisMonth)->sum('credit');

        $monthlyRevenue = AccountingJournalDetail::whereHas('account', fn ($q) => $q->where('account_code', 'like', '4%'))
            ->whereHas('journal', fn ($q) => $q->whereBetween('transaction_date', [$awalBulan, $akhirBulan]))
            ->sum('credit');

        return view('dashboard.index', compact(
            'events', 'totalEvent', 'upcomingEvent', 'totalRegistration', 'totalCheckedIn', 'totalPending',
            'cashAccounts', 'totalCashBank', 'cashInThisMonth', 'cashOutThisMonth', 'monthlyRevenue'
        ));
    }

    public function edit()
    {
        $user = auth()->user()->load('bank');
    
        $religions = Religion::all();
        $provinces = Province::all();
        $cities = City::where('province_id', $user->province_id)->get();
        $districts = District::where('city_id', $user->city_id)->get();
        $subDistricts = SubDistrict::where('district_id', $user->district_id)->get();
        $postalCodes = PostalCode::where('sub_district_id', $user->sub_district_id)->get();
        return view('members.profile', compact('user', 'religions', 'provinces', 'cities', 'districts', 'subDistricts', 'postalCodes'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        // Validasi input
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'required|in:1,2',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'required|date_format:Y-m-d',
            'identity_number' => [
                'required',
                'regex:/^[0-9]{16}$/',
                Rule::unique('users', 'identity_number')->ignore($user->id),
            ],
            'religion_id' => 'required|exists:religions,id',
            'npwp' => 'nullable|string|max:30',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'sub_district_id' => 'required|exists:sub_districts,id',
            'postal_code_id' => 'required|exists:postal_codes,id',
            'bank_id' => 'nullable|uuid|exists:banks,id',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|max:50',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $newPhotoPath = null;
        // Upload photos jika ada
        if ($request->hasFile('photo')) {
            $newPhotoPath = $request->file('photo')->storeAs(
                'photos',
                Str::uuid().'.'.$request->file('photo')->getClientOriginalExtension(),
                'public'
            );

            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $validated['photo'] = $newPhotoPath;
        }

        // 🔁 Update data user
        $user->update($validated);

        return redirect()->route('dashboard')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }

    private function memberDashboard($user)
{
    // Event yang saya daftar
    $myRegistrations = EventRegistration::with('event:id,name,start_at,end_at,location,quota')
        ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('registered_by', $user->id))
        ->latest('registered_at')
        ->get();

    // Event yang sedang berlangsung + jumlah pesertanya
    $ongoingEvents = Event::where('is_published', true)
        ->where('end_at', '>=', now())
        ->withCount([
            'registrations as registrations_count' => fn ($q) => $q->whereIn('status', self::COUNTED_STATUS),
        ])
        ->orderBy('start_at')
        ->get();

    return view('dashboard.member', compact('myRegistrations', 'ongoingEvents'));
}

        private function buildGroupedAccounts(\Closure $journalFilter)
{
    $accounts = AccountingAccount::where('is_parent', false)
        ->get()
        ->map(function ($account) use ($journalFilter) {

            $debit = AccountingJournalDetail::query()
                ->where('account_id', $account->id)
                ->whereHas('journal', $journalFilter)
                ->sum('debit');

            $credit = AccountingJournalDetail::query()
                ->where('account_id', $account->id)
                ->whereHas('journal', $journalFilter)
                ->sum('credit');

            switch ($account->category) {

                case 'AKTIVA':
                case 'BEBAN':
                    $balance = $debit - $credit;
                    break;

                case 'KEWAJIBAN':
                case 'EKUITAS':
                case 'PENDAPATAN':
                    $balance = $credit - $debit;
                    break;

                default:
                    $balance = $debit - $credit;
            }

            return [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_type' => $account->account_type,
                'category'     => $account->category,
                'sub_category' => $account->sub_category,
                'debit'        => $debit,
                'credit'       => $credit,
                'balance'      => $balance,
            ];
        });

    return $accounts
        ->groupBy('category')
        ->map(function ($catGroup) {

            return $catGroup->groupBy('sub_category')->map(function ($subGroup) {

                return [
                    'accounts'         => $subGroup,
                    'subtotalDebit'    => $subGroup->sum('debit'),
                    'subtotalCredit'   => $subGroup->sum('credit'),
                    'subtotalBalance'  => $subGroup->sum('balance'),
                ];

            });

        });
}
}
