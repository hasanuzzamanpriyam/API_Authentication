<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $adminPermissions = [
            'manage-users',
            'manage-blogs',
            'manage-settings',
        ];

        foreach ($adminPermissions as $perm) {
            DB::table('permissions')->insert([
                'name' => $perm,
                'guard_name' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }


        $userPermissions = [
            ['name' => 'product.create', 'display_name' => 'Create Product', 'description' => 'Can create products'],
            ['name' => 'product.edit', 'display_name' => 'Edit Product', 'description' => 'Can edit products'],
            ['name' => 'product.delete', 'display_name' => 'Delete Product', 'description' => 'Can delete products'],
            ['name' => 'product.view', 'display_name' => 'View Product', 'description' => 'Can view products'],

            ['name' => 'payment.create', 'display_name' => 'Create Payment', 'description' => 'Can create payments'],
            ['name' => 'payment.edit', 'display_name' => 'Edit Payment', 'description' => 'Can edit payments'],
            ['name' => 'payment.delete', 'display_name' => 'Delete Payment', 'description' => 'Can delete payments'],
            ['name' => 'payment.view', 'display_name' => 'View Payment', 'description' => 'Can view payments'],

            ['name' => 'blog.create', 'display_name' => 'Create Blog', 'description' => 'Can create blogs'],
            ['name' => 'blog.edit', 'display_name' => 'Edit Blog', 'description' => 'Can edit blogs'],
            ['name' => 'blog.delete', 'display_name' => 'Delete Blog', 'description' => 'Can delete blogs'],
            ['name' => 'blog.view', 'display_name' => 'View Blog', 'description' => 'Can view blogs'],
        ];

        foreach ($userPermissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Permissions seeded successfully.');
    }
}
