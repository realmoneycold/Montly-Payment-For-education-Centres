<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monthly_payments', function (Blueprint $table) {
            $table->string('status', 20)->default('unpaid')->after('paid_at');
            $table->decimal('paid_amount', 12, 2)->nullable()->after('status');
            $table->text('comment')->nullable()->after('paid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_payments', function (Blueprint $table) {
            $table->dropColumn(['status', 'paid_amount', 'comment']);
        });
    }
};
