<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('levels')->insertOrIgnore([
            ['name' => 'Beginner'],
            ['name' => 'Intermediate'],
            ['name' => 'Advanced'],
        ]);
    }

    public function down(): void
    {
        DB::table('levels')
            ->whereIn('name', ['Beginner', 'Intermediate', 'Advanced'])
            ->delete();
    }
};
