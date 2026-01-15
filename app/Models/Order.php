<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    public $incrementing = true;
    public $timestamps = false; // DB does not have timestamps

    protected $fillable = [
        'student_id',
        'product_id',
        'qty',
        'p_price',
        'status',
        'viewed',
        'date',
        'variation_id',
        'flag_delivered',
        'counter',
        'rp_order_id',
        'name_var',
        'variation_id',
        'flag',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'boolean',
        'viewed' => 'boolean',
        'p_price' => 'decimal:2',
    ];

    /**
     * Get the student that owns the order.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the product for the order.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
