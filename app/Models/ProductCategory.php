<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $table = 'product_categories';
    protected $hidden = ['image', 'created_at', 'updated_at'];
    protected $fillable = [
        'name',
        'image',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }
}
