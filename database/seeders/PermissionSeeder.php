<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Admin Permissions (guard: admin)
        |--------------------------------------------------------------------------
        */
        $adminPermissions = [
            'manage-users',
            'manage-products',
            'manage-blogs',
            'manage-payments',
        ];

        foreach ($adminPermissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'admin',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | User Permissions (guard: api)
        |--------------------------------------------------------------------------
        */
        $userPermissions = [
            'payment-view',
            'blog-view',
            'product-view',
        ];

        foreach ($userPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Roles & Assign Permissions
        |--------------------------------------------------------------------------
        */
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $userRole  = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);

        $adminRole->syncPermissions($adminPermissions);
        $userRole->syncPermissions($userPermissions);

        /*
        |--------------------------------------------------------------------------
        | Assign Roles to Default Accounts (if they exist)
        |--------------------------------------------------------------------------
        */
        $admin = Admin::find(1);
        if ($admin) {
            $admin->assignRole('admin');
        }

        $user = User::find(1);
        if ($user) {
            $user->assignRole('user');
        }

        $this->command->info('✅ Permissions and roles seeded successfully.');
    }
}
