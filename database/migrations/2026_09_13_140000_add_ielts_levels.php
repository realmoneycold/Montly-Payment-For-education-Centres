<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $levels = [
        'IELTS 1.0 - Non-user' => 'Non-user',
        'IELTS 1.5' => 'Limited user',
        'IELTS 2.0 - Intermittent user' => 'Intermittent user',
        'IELTS 2.5' => 'Intermittent user',
        'IELTS 3.0 - Extremely limited user' => 'Extremely limited user',
        'IELTS 3.5' => 'Extremely limited user',
        'IELTS 4.0 - Limited user' => 'Limited user',
        'IELTS 4.5' => 'Limited user',
        'IELTS 5.0 - Modest user' => 'Modest user',
        'IELTS 5.5' => 'Modest user',
        'IELTS 6.0 - Competent user' => 'Competent user',
        'IELTS 6.5' => 'Competent user',
        'IELTS 7.0 - Good user' => 'Good user',
        'IELTS 7.5' => 'Good user',
        'IELTS 8.0 - Very good user' => 'Very good user',
        'IELTS 8.5' => 'Very good user',
        'IELTS 9.0 - Expert user' => 'Expert user',
    ];

    public function up(): void
    {
        DB::table('levels')->insertOrIgnore(
            collect(array_keys($this->levels))
                ->map(fn (string $name): array => ['name' => $name])
                ->all()
        );
    }

    public function down(): void
    {
        DB::table('levels')
            ->whereIn('name', array_keys($this->levels))
            ->delete();
    }
};
