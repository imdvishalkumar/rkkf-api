<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    public $incrementing = true;

    protected $fillable = [
        'student_id',
        'product_id',
        'quantity',
        'amount',
        'status',
        'viewed',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'boolean',
        'viewed' => 'boolean',
        'amount' => 'decimal:2',
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
