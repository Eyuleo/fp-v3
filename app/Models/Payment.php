<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'application_fee_id',
        'transfer_id',
        'amount',
        'commission',
        'net_amount',
        'status',
        'processed_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount'       => 'decimal:2',
        'commission'   => 'decimal:2',
        'net_amount'   => 'decimal:2',
        'processed_at' => 'datetime',
        'metadata'     => 'array',
    ];

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
