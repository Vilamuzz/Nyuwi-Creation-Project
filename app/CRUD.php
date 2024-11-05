<?php

namespace App;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class CRUD
{
    public function create(array $data)
    {
        return Product::create($data);
    }

    public function read($id)
    {
        return Product::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return true;
    }
}
