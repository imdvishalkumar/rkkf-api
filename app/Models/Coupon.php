<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $table = 'coupon';
    protected $primaryKey = 'coupon_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'coupon_txt',
        'amount',
        'used',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Get the fees for the coupon.
     */
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'coupon_id');
    }
}
