<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'global' => [
                'no-permission',
                'panel-user-managment',
                'master-module',
            ],
            'users' => [
                'users-list',
                'users-create',
                'users-edit',
                'users-delete',
            ],
            'roles' => [
                'roles-list',
                'roles-create',
                'roles-edit',
                'roles-delete',
            ],
            'permissions' => [
                'permissions-list',
                'permissions-create',
                'permissions-edit',
                'permissions-delete',
            ],
            'country' => [
                'country-list',
                'country-create',
                'country-edit',
                'country-delete',
            ],
            'state' => [
                'state-list',
                'state-create',
                'state-edit',
                'state-delete',
            ],
            'city' => [
                'city-list',
                'city-create',
                'city-edit',
                'city-delete',
            ],
            'Pincode' => [
                'pincode-list',
                'pincode-create',
                'pincode-edit',
                'pincode-delete',
            ],
            'Sliders' => [
                'sliders-list',
                'sliders-create',
                'sliders-edit',
                'sliders-delete',
            ],
            'Document-type' => [
                'document-type-list',
                'document-type-create',
                'document-type-edit',
                'document-type-delete',
            ],
            'Documents' => [
                'documents-list',
                'documents-create',
                'documents-edit',
                'documents-delete',
            ],
            'Application' => [
                'about-us',
            ],
            'Setting' => [
                'generate-api-documents',
            ]
        ];

        foreach ($permissions as $groupKey => $permissionGroup) {
            foreach($permissionGroup as $key => $permission){
                // dd($groupKey, $permissionGroup, $key, $permission);
                Permission::create(['group' => $groupKey, 'name' => $permission]);
            }
        }
    }
}
