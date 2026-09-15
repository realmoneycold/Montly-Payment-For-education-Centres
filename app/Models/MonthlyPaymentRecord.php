<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyPaymentRecord extends Model
{
    use HasFactory;

    protected $table = 'monthly_payments';

    protected $fillable = [
        'enrollment_id',
        'month',
        'paid_at',
        'status',
        'paid_amount',
        'comment',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'paid_amount' => 'decimal:2',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}
