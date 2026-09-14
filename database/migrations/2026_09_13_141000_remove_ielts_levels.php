<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('levels')
            ->where('name', 'like', 'IELTS%')
            ->delete();
    }

    public function down(): void
    {
        // IELTS levels are intentionally not restored.
    }
};
