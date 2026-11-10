<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ActiveRole;
use App\Models\Vendor;
use App\Models\Religion;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\ProductColor;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = auth()->user();

        $query = Vendor::with(['user.roles']);

        if (!ActiveRole::hasPermission('lihat daftar vendor')) {
            $query->where('user_id', $auth->id);
        }

        if ($request->ajax()) {
            $vendors = $query->get();

        return DataTables::of($vendors)
                ->addIndexColumn()
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname ?? '-';
                })
                ->addColumn('email', function ($row) {
                    return $row->user->email ?? '-';
                })

                ->addColumn('name', function ($row) {
                    return $row->name ?? '-';
                })

                ->addColumn('address', function ($row) {
                    return $row->address ?? '-';
                })

                ->addColumn('phone', function ($row) {
                    return $row->phone ?? '-';
                })
                
                ->editColumn('fullname', function ($row) {
                    $url = route('vendors.show', $row->id);
                    $name = Str::title($row->user->fullname ?? '-');
                    return '<a href="'.$url.'">'.e($name).'</a>';
                })
                
                ->addColumn('action', function ($vendor) {
                    $buttons = '';
                    if (ActiveRole::hasPermission('ubah data vendor')) {
                        $buttons .= '<a href="' . route('vendors.edit', $vendor->id) . '" class="btn btn-icon btn-sm btn-warning me-1" title="Ubah">
                                        <i class="ti ti-edit"></i>
                                    </a>';
                    }
                    if (ActiveRole::hasPermission('lihat data vendor')) {
                        $buttons .= '<a href="' . route('vendors.show', $vendor->id) . '" class="btn btn-icon btn-sm btn-primary me-1" title="Lihat">
                                        <i class="ti ti-eye"></i>
                                    </a>';

                    }
                    if (ActiveRole::hasPermission('hapus data vendor')) {
                        $buttons .= '<button data-id="' . $vendor->id . '" class="btn btn-icon btn-sm btn-danger delete-vendors" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['fullname', 'action'])
                ->make(true);
        }

        return view('vendors.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Vendor $vendor)
    {
        $user = auth()->user();
        $religions = Religion::all();
        $provinces = Province::all();
        $roles = Role::all();
        $externalRoles = Role::where('role_group', 'Eksternal')
            ->orderBy('name')
            ->pluck('name');
        return view('vendors.create', compact('vendor', 'user', 'roles', 'externalRoles', 'religions', 'provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
        'identity_number' => 'nullable|regex:/^[0-9]{16}$/|unique:users,identity_number',
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

        // --- data vendor ---
        'vendor_id' => 'required|unique:vendors,vendor_id',
        'vendor_name' => 'nullable|string|max:255',
        'vendor_phone' => 'nullable|string|max:20',
        'vendor_address' => 'nullable|string|max:255',
        'province_id' => 'nullable|exists:provinces,id',
        'city_id' => 'nullable|exists:cities,id',
        'district_id' => 'nullable|exists:districts,id',
        'sub_district_id' => 'nullable|exists:sub_districts,id',
        'postal_code_id' => 'nullable|exists:postal_codes,id',
        'role' => 'required|array',
        'role.*' => 'string|exists:roles,name',
    ]);

    // Jika alamat pengiriman sama dengan domisili user
        if ($request->has('same_address')) {
            $validated['province_id'] = $validated['user_province_id'];
            $validated['city_id'] = $validated['user_city_id'];
            $validated['district_id'] = $validated['user_district_id'];
            $validated['sub_district_id'] = $validated['user_sub_district_id'];
            $validated['postal_code_id'] = $validated['user_postal_code_id'];
            $validated['vendor_address'] = $validated['address'];
            $validated['vendor_name'] = $validated['fullname'];
            $validated['vendor_phone'] = $validated['phone'];
        }

    DB::transaction(function () use ($validated, $request) {
        if ($request->hasFile('photo')) {
            $filename = Str::uuid().'.'.$request->file('photo')->getClientOriginalExtension();

            $path = $request->file('photo')->storeAs(
                'photos',
                $filename,
                'public'
            );

            $validated['photo'] = $path;   // → photos/uuid.jpg
        }

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

            session()->flash('new_user_password', $password);
        }

        // 🔹 Assign role tanpa hapus role lama
        if (!empty($validated['role'])) {
            foreach ($validated['role'] as $r) {
                if (!$user->hasRole($r)) {
                    $user->assignRole($r);
                }
            }
        }

        Vendor::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'vendor_id' => $validated['vendor_id'],
            'vendor_name' => $validated['vendor_name'],
            'vendor_phone' => $validated['vendor_phone'],
            'vendor_address' => $validated['vendor_address'],
            'province_id' => $validated['province_id'],
            'city_id' => $validated['city_id'],
            'district_id' => $validated['district_id'],
            'sub_district_id' => $validated['sub_district_id'],
            'postal_code_id' => $validated['postal_code_id'],
        ]);
    });

    return redirect()
        ->route('vendors.index')
        ->with('success', 'Data vendor berhasil ditambahkan.');
}

public function generateVendorIdAjax()
{
    $lastNumber = Vendor::selectRaw("MAX(CAST(SUBSTRING(vendor_id, 3) AS INTEGER)) as max_vendor_id")->value('max_vendor_id');
    $newNumber = ($lastNumber ?? 0) + 1;

    $newvendorId = 'V-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

    return response()->json([
        'vendor_id' => $newvendorId
    ]);
}
     public function show(Vendor $vendor)
    {
        $vendor->load('user');
        // $productColors = $product->colors->pluck('id')->toArray();
        return view('vendors.show', [
            'user' => $vendor->user,
            'vendor' => $vendor,
        ]);
    }

    public function edit($id)
    {
        $vendor = Vendor::with(['user.roles', 'user.bank'])->findOrFail($id);
        $user = $vendor->user;
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
        
        return view('vendors.edit', compact('user', 'roles', 'selectedRoles', 'vendor', 'externalRoles',
        'religions', 'provinces', 'cities', 'districts', 'subDistricts', 'postalCodes'));
    }

    public function update(Request $request, Vendor $vendor)
{
    $validated = $request->validate([
        // --- data user ---
        'fullname' => 'required|string|max:255',
        'nickname' => 'nullable|string|max:100',
        'gender' => 'nullable|in:1,2',
        'email' => 'required|email|unique:users,email,' . $vendor->user_id,
        'birth_place' => 'nullable|string|max:100',
        'birth_date' => 'nullable|date_format:Y-m-d',
        'identity_number' => [
            'nullable',
            'regex:/^[0-9]{16}$/',
            Rule::unique('users', 'identity_number')->ignore($vendor->user_id),
        ],
        'religion_id' => 'nullable|exists:religions,id',
        'npwp' => 'nullable|string|max:30',
        'phone' => 'nullable|string|max:20',
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

        // --- data vendor ---
        'vendor_id' => 'required|string|max:50|unique:vendors,vendor_id,' . $vendor->id,
        'vendor_name' => 'nullable|string|max:255',
        'vendor_phone' => 'nullable|string|max:20',
        'vendor_address' => 'nullable|string|max:255',
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
            $validated['vendor_address'] = $validated['address'];
            $validated['vendor_name'] = $validated['fullname'];
            $validated['vendor_phone'] = $validated['phone'];
        }

    $newPhotoPath = null;

    DB::transaction(function () use ($validated, $vendor, $request) {
        $user = $vendor->user;

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
            // Pastikan minimal masih punya role vendor
            if (!$user->hasRole('Vendor')) {
                $user->assignRole('Vendor');
            }
        }
        // 🔹 Update data vendor
        $vendor->update([
            'vendor_id' => $validated['vendor_id'],
            'vendor_name' => $validated['vendor_name'],
            'vendor_phone' => $validated['vendor_phone'],
            'vendor_address' => $validated['vendor_address'],
            'province_id' => $validated['province_id'],
            'city_id' => $validated['city_id'],
            'district_id' => $validated['district_id'],
            'sub_district_id' => $validated['sub_district_id'],
            'postal_code_id' => $validated['postal_code_id'],
            'photo' => $validated['photo'] ?? $vendor->photo,
        ]);
    });

    return redirect()
        ->route('vendors.show', $vendor->id)
        ->with('success', 'Data vendor berhasil diperbarui.');
}

    public function destroy(Vendor $vendor): JsonResponse
{
    DB::transaction(function () use ($vendor) {
        $user = $vendor->user;

        // 🔹 Hapus foto architect dari storage kalau ada
        if ($vendor->photo && Storage::disk('public')->exists('photos/' . $vendor->photo)) {
            Storage::disk('public')->delete('photos/' . $vendor->photo);
        }

        // 🔹 Hapus record architect
        $vendor->delete();

        // 🔹 Cek user yang terhubung
        if ($user) {
            $roles = $user->roles->pluck('name')->toArray();

            // Kalau hanya punya role "architect"
            if (count($roles) === 1 && in_array('Vendor', $roles)) {

                // Hapus juga foto user kalau ada
                if ($user->photo && Storage::disk('public')->exists('photos/' . $user->photo)) {
                    Storage::disk('public')->delete('photos/' . $user->photo);
                }

                // Hapus akun user
                $user->delete();

            } else {
                // Kalau user masih punya role lain, hapus hanya role architect-nya
                $user->removeRole('Vendor');
            }
        }
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Data architect berhasil dihapus.'
    ]);
}
}
