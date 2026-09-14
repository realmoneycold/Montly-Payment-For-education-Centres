<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\MonthlyPaymentRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyPayments extends Command
{
    protected $signature = 'app:generate-monthly-payments
                            {--month= : The month to generate records for in Y-m format (defaults to current month)}';

    protected $description = 'Generate monthly payment records (Not Paid) for all active enrollments';

    public function handle(): int
    {
        $month = $this->option('month') ?? Carbon::now()->format('Y-m');

        // Validate format
        try {
            Carbon::createFromFormat('Y-m', $month);
        } catch (\Exception) {
            $this->error("Invalid month format: {$month}. Use Y-m (e.g. 2026-09).");

            return self::FAILURE;
        }

        $this->info("Generating monthly payment records for: {$month}");

        // Only active enrollments (status_id 1 = Not Paid, 2 = Paid — both still active)
        $enrollments = Enrollment::whereIn('status_id', [1, 2])
            ->whereNull('parent_id')
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($enrollments as $enrollment) {
            $exists = MonthlyPaymentRecord::where('enrollment_id', $enrollment->id)
                ->where('month', $month)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            MonthlyPaymentRecord::create([
                'enrollment_id' => $enrollment->id,
                'month' => $month,
                'paid_at' => null,
            ]);

            $created++;
        }

        $this->info("Created: {$created} | Skipped (already existed): {$skipped}");

        return self::SUCCESS;
    }
}
