<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'developer',
            'super-admin'
        ];

        foreach ($roles as $role) {
            $role_create = Role::create(['name' => $role]);

            $permissions = Permission::pluck('id','id')->all();

            $role_create->syncPermissions($permissions);
        }
    }
}
