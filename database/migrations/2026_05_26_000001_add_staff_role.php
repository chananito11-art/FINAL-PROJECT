<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::firstOrCreate(
            ['name' => 'staff', 'guard_name' => 'web'],
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::where('name', 'staff')->where('guard_name', 'web')->delete();
    }
};
