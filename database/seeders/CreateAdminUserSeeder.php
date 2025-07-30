<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [[
            'name' => 'Nimit Work',
            'email' => 'nimit.work.1922@gmail.com',
            'phone' => '9978911174',
            'sp' => 'nimit@123',
            'status' => 'active',
            'password' => bcrypt('nimit@123'),
            'created_by' => '0'
        ], [
            'name' => 'Ocean Office',
            'email' => 'admin.ocean@gmail.com',
            'phone' => '123456789',
            'sp' => 'Ocean@2025',
            'status' => 'active',
            'password' => bcrypt('Ocean@2025'),
            'created_by' => '0'
        ]];

        foreach ($users as $user_data) {
            $user = User::create($user_data);
        }

        $role = Role::where('name', 'developer')->first();
        $user = User::where('email', 'nimit.work.1922@gmail.com')->first();
        $user->assignRole([$role->id]);
        if ($role && $role?->permissions->count()) {
            $user->syncPermissions($role?->permissions->pluck('name'));
        }

        $role = Role::where('name', 'super-admin')->first();
        $user = User::where('email', 'admin.ocean@gmail.com')->first();
        $user->assignRole([$role->id]);
    }
}
