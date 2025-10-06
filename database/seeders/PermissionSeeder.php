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
        | Create Roles
        |--------------------------------------------------------------------------
        */
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);


        /*
        |--------------------------------------------------------------------------
        | Assign Permissions to Roles
        |--------------------------------------------------------------------------
        */
        // Super admin gets all 'admin' guard permissions
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'admin')->get());

        // Admin gets specific permissions
        $adminRole->syncPermissions(['manage-products', 'manage-blogs']);

        // Manager gets a subset of permissions
        $managerRole->syncPermissions(['manage-products']);

        // Cashier gets specific permissions
        $cashierRole->syncPermissions(['manage-payments']);

        // Regular user gets specific permissions for 'api' guard
        $userRole->syncPermissions(['payment-view', 'blog-view', 'product-view']);


        /*
        |--------------------------------------------------------------------------
        | Assign Roles to Default Accounts (if they exist)
        |--------------------------------------------------------------------------
        */
        // Find the first admin user and assign the super_admin role
        $superAdmin = Admin::find(1);
        if ($superAdmin) {
            $superAdmin->assignRole('super_admin');
        }

        // Find the first regular user and assign the user role
        $regularUser = User::find(1);
        if ($regularUser) {
            $regularUser->assignRole('user');
        }
    }
}
