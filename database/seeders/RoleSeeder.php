<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['name' => 'admin'],
            ['name' => 'editor'],
            ['name' => 'customer'],
        ];
        DB::table('roles')->insert($roles);

        $permissions = [
            ['name' => 'view_user'],
            ['name' => 'create_user'],
            ['name' => 'edit_user'],
            ['name' => 'delete_user'],
            ['name' => 'create_post'],
            ['name' => 'update_post'],
            ['name' => 'delete_post'],
            ['name' => 'create_category'],
            ['name' => 'update_category'],
            ['name' => 'delete_category'],

        ];
        DB::table('permissions')->insert($permissions);

        $rolePermissions = [
            ['role_id' => 1, 'permission_id' => 1],
            ['role_id' => 1, 'permission_id' => 2],
            ['role_id' => 1, 'permission_id' => 3],
            ['role_id' => 1, 'permission_id' => 4],
            ['role_id' => 1, 'permission_id' => 5],
            ['role_id' => 1, 'permission_id' => 6],
            ['role_id' => 1, 'permission_id' => 7],
            ['role_id' => 1, 'permission_id' => 8],
            ['role_id' => 1, 'permission_id' => 9],
            ['role_id' => 1, 'permission_id' => 10],
            ['role_id' => 2, 'permission_id' => 5],
            ['role_id' => 2, 'permission_id' => 6],
            ['role_id' => 2, 'permission_id' => 8],
            ['role_id' => 2, 'permission_id' => 9],
        ];
        DB::table('roles_permissions')->insert($rolePermissions);
    }
}
