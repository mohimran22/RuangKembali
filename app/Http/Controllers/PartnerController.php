<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;
use App\Models\User;
use App\Models\Religion;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = auth()->user();

        $query = Partner::with(['user.roles']);

        if ($auth->can('lihat data arsitek') && !$auth->can('lihat daftar arsitek')) {
            $query->where('user_id', $auth->id);
        }

        if ($request->ajax()) {
            $partners = $query->get();

        return DataTables::of($partners)
                ->addIndexColumn()

                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname ?? '-';
                })
                ->addColumn('email', function ($row) {
                    return $row->user->email ?? '-';
                })
                ->addColumn('architect_name', fn($row) => $row->architect_name ?? '-')
                ->addColumn('architect_phone', fn($row) => $row->architect_phone ?? '-')
                ->addColumn('architect_address', fn($row) => $row->architect_address ?? '-')
                ->addColumn('province_name', fn($row) => $row->province->name ?? '-')
                ->addColumn('city_name', fn($row) => $row->city->name ?? '-')
                ->addColumn('district_name', fn($row) => $row->district->name ?? '-')
                ->addColumn('sub_district_name', fn($row) => $row->subDistrict->name ?? '-')
                ->addColumn('postal_code', fn($row) => $row->postalCode->postal_code ?? '-')

                ->editColumn('fullname', function ($row) {
                    $url = route('partners.show', $row->id);
                    $name = Str::title($row->user->fullname ?? '-');
                    return '<a href="'.$url.'">'.e($name).'</a>';
                })
                
                ->addColumn('action', function ($partner) {
                    $buttons = '';
                    if (auth()->user()->can('ubah data mitra')) {
                        $buttons .= '<a href="' . route('partners.edit', $partner->id) . '" class="btn btn-icon btn-sm btn-warning me-1" title="Ubah">
                                        <i class="ti ti-edit"></i>
                                    </a>';
                    }
                    if (auth()->user()->can('lihat data mitra')) {
                        $buttons .= '<a href="' . route('partners.show', $partner->id) . '" class="btn btn-icon btn-sm btn-primary me-1" title="Lihat">
                                        <i class="ti ti-eye"></i>
                                    </a>';

                    }
                    if (auth()->user()->can('hapus data mitra')) {
                        $buttons .= '<button data-id="' . $partner->id . '" class="btn btn-icon btn-sm btn-danger delete-architect" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['fullname', 'action'])
                ->make(true);
        }

        return view('partners.index');
    }

            public function create()
    {
        $user = auth()->user();
        $religions = Religion::all();
        $provinces = Province::all();
        $externalRoles = Role::where('role_group', 'Eksternal')
            ->orderBy('name')
            ->pluck('name');
        return view('partners.create', compact('user', 'externalRoles', 'religions', 'provinces'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'nullable|exists:users,id',
        'fullname' => 'required|string|max:255',
        'nickname' => 'nullable|string|max:100',
        'gender' => 'nullable|in:1,2',
        'email' => 'required|email|unique:users,email',
        'birth_place' => 'nullable|string|max:100',
        'birth_date' => 'nullable|date_format:Y-m-d',
        'identity_number' => 'nullable|string|max:16',
        'religion_id' => 'nullable|exists:religions,id',
        'npwp' => 'nullable|numeric|max:30',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:255',
        'user_province_id' => 'nullable|exists:provinces,id',
        'user_city_id' => 'nullable|exists:cities,id',
        'user_district_id' => 'nullable|exists:districts,id',
        'user_sub_district_id' => 'nullable|exists:sub_districts,id',
        'user_postal_code_id' => 'nullable|exists:postal_codes,id',
        'bank_id' => 'nullable|uuid|exists:banks,id',
        'account_number' => 'nullable|string|max:50',
        'account_holder' => 'nullable|string|max:50',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        // --- data architect ---
        'architect_id' => 'required|unique:architects,architect_id',
        'architect_name' => 'nullable|string|max:255',
        'architect_phone' => 'nullable|string|max:20',
        'architect_address' => 'nullable|string|max:255',
        'province_id' => 'nullable|exists:provinces,id',
        'city_id' => 'nullable|exists:cities,id',
        'district_id' => 'nullable|exists:districts,id',
        'sub_district_id' => 'nullable|exists:sub_districts,id',
        'postal_code_id' => 'nullable|exists:postal_codes,id',
        'role' => 'required|array',
        'role.*' => 'string|exists:roles,name',
    ]);

    if ($request->hasFile('photo')) {
        $filename = Str::uuid().'.'.$request->file('photo')->getClientOriginalExtension();

        $path = $request->file('photo')->storeAs(
            'photos',
            $filename,
            'public'
        );

        // simpan full relative path
        $validated['photo'] = $path;   // → photos/uuid.jpg
    }

    // Jika alamat pengiriman sama dengan domisili user
        if ($request->has('same_address')) {
            $validated['province_id'] = $validated['user_province_id'];
            $validated['city_id'] = $validated['user_city_id'];
            $validated['district_id'] = $validated['user_district_id'];
            $validated['sub_district_id'] = $validated['user_sub_district_id'];
            $validated['postal_code_id'] = $validated['user_postal_code_id'];
            $validated['architect_address'] = $validated['address'];
            $validated['architect_name'] = $validated['fullname'];
            $validated['architect_phone'] = $validated['phone'];
        }

    DB::transaction(function () use ($validated, $request) {

        // 🔹 Cek user atau buat baru
        if (!empty($validated['user_id'])) {
            $user = User::find($validated['user_id']);
        } else {
            $password = '12345678';
            $user = User::create([
                'id' => Str::uuid(),
                'fullname' => $validated['fullname'],
                'nickname' => $validated['nickname'],
                'birth_place' => $validated['birth_place'],
                'birth_date' => $validated['birth_date'],
                'identity_number' => $validated['identity_number'],
                'npwp' => $validated['npwp'],
                'address' => $validated['address'],
                'religion_id' => $validated['religion_id'],
                'province_id' => $validated['user_province_id'],
                'city_id' => $validated['user_city_id'],
                'district_id' => $validated['user_district_id'],
                'sub_district_id' => $validated['user_sub_district_id'],
                'postal_code_id' => $validated['user_postal_code_id'],
                'email' => $validated['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'photo' => $validated['photo'] ?? null,
                'bank_id' => $validated['bank_id'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'account_holder' => $validated['account_holder'] ?? null,
            ]);
        }

        // 🔹 Assign role tanpa hapus role lama
        if (!empty($validated['role'])) {
            foreach ($validated['role'] as $r) {
                if (!$user->hasRole($r)) {
                    $user->assignRole($r);
                }
            }
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('photos', $filename, 'public');
            $validated['photo'] = $filename;
        }

        Partner::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'architect_id' => $validated['architect_id'],
            'architect_name' => $validated['architect_name'],
            'architect_phone' => $validated['architect_phone'],
            'architect_address' => $validated['architect_address'],
            'province_id' => $validated['province_id'],
            'city_id' => $validated['city_id'],
            'district_id' => $validated['district_id'],
            'sub_district_id' => $validated['sub_district_id'],
            'postal_code_id' => $validated['postal_code_id'],
        ]);
    });

    return redirect()
        ->route('partners.index')
        ->with('success', 'Data mitra berhasil ditambahkan.');
}


public function generateArchitectIdAjax()
{
    $lastNumber = Partner::selectRaw("MAX(CAST(SUBSTRING(architect_id, 3) AS INTEGER)) as max_architect_id")->value('max_architect_id');
    $newNumber = ($lastNumber ?? 0) + 1;

    $newArchitectId = 'MIT-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

    return response()->json([
        'architect_id' => $newArchitectId
    ]);
}
         public function show(Partner $partner)
    {
        $partner->load('user');
        return view('partners.show', [
            'user' => $partner->user,
            'partner' => $partner
        ]);
    }

   
    public function edit($id)
    {
        $partner = Partner::with(['user.roles', 'user.bank'])->findOrFail($id);
        $user = $partner->user;
        $religions = Religion::all();
        $provinces = Province::all();
        $roles = Role::all();
        $externalRoles = Role::where('role_group', 'Eksternal')
            ->orderBy('name')
            ->pluck('name');
        $selectedRoles = $user->roles->pluck('name')->toArray();
        $cities = City::where('province_id', $user->province_id)->get();
        $districts = District::where('city_id', $user->city_id)->get();
        $subDistricts = SubDistrict::where('district_id', $user->district_id)->get();
        $postalCodes = PostalCode::where('sub_district_id', $user->sub_district_id)->get();
        
        return view('partners.edit', compact('user', 'roles', 'selectedRoles', 'partner', 'externalRoles',
        'religions', 'provinces', 'cities', 'districts', 'subDistricts', 'postalCodes'));
    }

   
    public function update(Request $request, Partner $partner)
{
    $validated = $request->validate([
        // --- data user ---
        'fullname' => 'required|string|max:255',
        'nickname' => 'nullable|string|max:100',
        'gender' => 'nullable|in:1,2',
        'email' => 'required|email|unique:users,email,' . $partner->user_id,
        'birth_place' => 'nullable|string|max:100',
        'birth_date' => 'nullable|date_format:Y-m-d',
        'identity_number' => 'nullable|string|max:16',
        'religion_id' => 'nullable|exists:religions,id',
        'npwp' => 'nullable|string|max:30',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:255',
        'user_province_id' => 'nullable|exists:provinces,id',
        'user_city_id' => 'nullable|exists:cities,id',
        'user_district_id' => 'nullable|exists:districts,id',
        'user_sub_district_id' => 'nullable|exists:sub_districts,id',
        'user_postal_code_id' => 'nullable|exists:postal_codes,id',
        'bank_id' => 'nullable|uuid|exists:banks,id',
        'account_number' => 'nullable|string|max:50',
        'account_holder' => 'nullable|string|max:50',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        // --- data architect ---
        'architect_id' => 'required|string|max:50|unique:architects,architect_id,' . $partner->id,
        'architect_name' => 'nullable|string|max:255',
        'architect_phone' => 'nullable|string|max:20',
        'architect_address' => 'nullable|string|max:255',
        'province_id' => 'nullable|exists:provinces,id',
        'city_id' => 'nullable|exists:cities,id',
        'district_id' => 'nullable|exists:districts,id',
        'sub_district_id' => 'nullable|exists:sub_districts,id',
        'postal_code_id' => 'nullable|exists:postal_codes,id',
        'role' => 'nullable|array',
        'role.*' => 'string|exists:roles,name',
    ]);

    if ($request->has('same_address')) {
            $validated['province_id'] = $validated['user_province_id'];
            $validated['city_id'] = $validated['user_city_id'];
            $validated['district_id'] = $validated['user_district_id'];
            $validated['sub_district_id'] = $validated['user_sub_district_id'];
            $validated['postal_code_id'] = $validated['user_postal_code_id'];
            $validated['architect_address'] = $validated['address'];
            $validated['architect_name'] = $validated['fullname'];
            $validated['architect_phone'] = $validated['phone'];
        }

    DB::transaction(function () use ($validated, $partner, $request) {
        $user = $partner->user;

        // 🔹 Upload foto baru (hapus lama jika ada)
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

        // 🔹 Update user data
        $user->update([
            'fullname' => $validated['fullname'],
            'nickname' => $validated['nickname'],
            'birth_place' => $validated['birth_place'],
            'birth_date' => $validated['birth_date'],
            'identity_number' => $validated['identity_number'],
            'religion_id' => $validated['religion_id'],
            'npwp' => $validated['npwp'],
            'address' => $validated['address'],
            'province_id' => $validated['user_province_id'],
            'city_id' => $validated['user_city_id'],
            'district_id' => $validated['user_district_id'],
            'sub_district_id' => $validated['user_sub_district_id'],
            'postal_code_id' => $validated['user_postal_code_id'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'bank_id' => $validated['bank_id'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'account_holder' => $validated['account_holder'] ?? null,
            'photo' => $validated['photo'] ?? $user->photo,
        ]);

        // 🔹 Role management: tetap multi-role aman
        if (!empty($validated['role'])) {
            foreach ($validated['role'] as $r) {
                if (!$user->hasRole($r)) {
                    $user->assignRole($r);
                }
            }
        } else {
            // Pastikan minimal masih punya role architect
            if (!$user->hasRole('Mitra Arsitek')) {
                $user->assignRole('Mitra Arsitek');
            }
        }

        $partner->update([
            'architect_id' => $validated['architect_id'],
            'architect_name' => $validated['architect_name'],
            'architect_phone' => $validated['architect_phone'],
            'architect_address' => $validated['architect_address'],
            'province_id' => $validated['province_id'],
            'city_id' => $validated['city_id'],
            'district_id' => $validated['district_id'],
            'sub_district_id' => $validated['sub_district_id'],
            'postal_code_id' => $validated['postal_code_id'],
            'photo' => $validated['photo'] ?? $partner->photo,
        ]);
    });

    return redirect()
        ->route('partners.show', $partner->id)
        ->with('success', 'Data architect berhasil diperbarui.');
}

       public function destroy(Partner $partner): JsonResponse
{
    DB::transaction(function () use ($partner) {
        $user = $partner->user;

        // 🔹 Hapus foto architect dari storage kalau ada
        if ($partner->photo && Storage::disk('public')->exists('photos/' . $partner->photo)) {
            Storage::disk('public')->delete('photos/' . $partner->photo);
        }

        // 🔹 Hapus record architect
        $partner->delete();

        // 🔹 Cek user yang terhubung
        if ($user) {
            $roles = $user->roles->pluck('name')->toArray();

            // Kalau hanya punya role "architect"
            if (count($roles) === 1 && in_array('Mitra', $roles)) {

                // Hapus juga foto user kalau ada
                if ($user->photo && Storage::disk('public')->exists('photos/' . $user->photo)) {
                    Storage::disk('public')->delete('photos/' . $user->photo);
                }

                // Hapus akun user
                $user->delete();

            } else {
                // Kalau user masih punya role lain, hapus hanya role architect-nya
                $user->removeRole('Mitra');
            }
        }
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Data architect berhasil dihapus.'
    ]);
}
}
