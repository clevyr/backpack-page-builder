<?php

use Backpack\PermissionManager\app\Models\Permission;
use Backpack\PermissionManager\app\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\PermissionRegistrar as PermissionRegistrarAlias;

/**
 * Class RolePermissionsSeeder
 */
class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles & permissions
        app()[PermissionRegistrarAlias::class]
            ->forgetCachedPermissions();

        // Create Permissions
        Permission::insert([
            [
                'name' => 'View Admin Panel',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'View Page List',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Create Page',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Edit Page',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Delete Page',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'View User List',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Create User',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Edit User',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Delete User',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'View Permission List',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Create Permission',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Edit Permission',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Delete Permission',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'View Role List',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Create Role',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Edit Role',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Delete Role',
                'guard_name' => backpack_guard_name(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Super admin role
        Role::create(['name' => 'Super Admin'])
            ->givePermissionTo(Permission::all());

        // Admin role
        Role::create(['name' => 'Admin'])
            ->givePermissionTo(Permission::all());

        // Page Manager Role
        Role::create(['name' => 'Page Manager'])
            ->givePermissionTo(['View Page List', 'Create Page', 'Edit Page', 'Delete Page']);

        // Create User Role
        Role::create(['name' => 'User']);
    }
}
