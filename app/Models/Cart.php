<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';
    protected $primaryKey = 'cart_id';
    public $timestamps = false; // Table does not have timestamps

    protected $fillable = [
        'student_id',
        'product_id',
        'variation_id',
        'qty',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class, 'variation_id', 'id');
    }
}
