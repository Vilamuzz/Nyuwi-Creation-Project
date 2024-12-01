<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'category_id',
        'stock',
        'price',
        'description',
        'image'
    ];

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship with Cart
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    // Relationship with ProductSize
    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    // Relationship with ProductColor
    public function colors()
    {
        return $this->hasMany(ProductColor::class);
    }

    // Relationship with OrderItem
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relationship with ProductReview
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }
}
