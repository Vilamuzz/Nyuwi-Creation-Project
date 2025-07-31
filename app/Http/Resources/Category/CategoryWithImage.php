<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Product;

class CategoryWithImage extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $firstProduct = Product::where('category_id', $this->id)
            ->whereNotNull('images')
            ->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $firstProduct && $firstProduct->images && is_array($firstProduct->images) && count($firstProduct->images) > 0
                ? $firstProduct->images[0]
                : null
        ];
    }
}
