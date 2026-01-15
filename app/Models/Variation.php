<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Variation extends Model
{
    protected $table = 'variation';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'product_id',
        'variation',
        'price',
        'qty',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'qty' => 'integer',
    ];

    /**
     * Get the product that owns the variation.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
