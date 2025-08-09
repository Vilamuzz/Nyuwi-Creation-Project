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
        'slug',
        'category_id',
        'stock',
        'price',
        'weight',
        'description',
        'images',
        'sizes',
        'colors',
    ];

    protected $casts = [
        'images' => 'array',
        'sizes' => 'array',
        'colors' => 'array',
        'price' => 'decimal:2',
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
