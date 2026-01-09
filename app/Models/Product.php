<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    public $incrementing = true;

    protected $fillable = [
        'name',
        'details',
        'image1',
        'image2',
        'image3',
        'belt_ids',
        'is_active',
        'product_category_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the product category that owns the product.
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Get the variations for the product.
     */
    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class, 'product_id');
    }
}
