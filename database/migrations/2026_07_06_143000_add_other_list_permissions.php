<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'other-list-list' => 'Other-list',
            'other-list-selection-list' => 'Other-list-selection',
        ];

        foreach ($permissions as $permission => $group) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['group' => $group]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::whereIn('name', [
            'other-list-list',
            'other-list-selection-list',
        ])->delete();
    }
};
