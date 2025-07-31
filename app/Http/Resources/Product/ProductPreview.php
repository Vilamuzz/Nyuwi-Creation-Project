<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPreview extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'price' => $this->price,
            'category_id' => $this->category_id,
            'images' => $this->images && is_array($this->images)
                ? $this->images
                : [],
            'image' => $this->images && is_array($this->images) && count($this->images) > 0
                ? $this->images[0]
                : null,
            'average_rating' => round($this->average_rating ?? 0, 1),
            'total_reviews' => $this->total_reviews ?? 0,
        ];
    }
}
