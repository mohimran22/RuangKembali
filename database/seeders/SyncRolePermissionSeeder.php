<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SyncRolePermissionSeeder extends Seeder
{
    public function run()
    {
        $role = Role::where('name', 'Pemilik Lisensi')->first();

        if ($role) {
            // Tambah permission jika belum ada
            $permission = Permission::firstOrCreate(['name' => 'role.tambah']);

            // Beri permission ke role (tanpa hapus yang lama)
            $role->givePermissionTo($permission);

            // Atau: Sync semua permission baru (HATI-HATI: ini replace semua!)
            // $role->syncPermissions(['edit articles', 'delete articles']);
        }
    }
}
