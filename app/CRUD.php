<?php

namespace App;

use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CRUD
{
    public function create(array $data)
    {
        $this->validateData($data);
        return Product::create($data);
    }

    public function read($id)
    {
        $product = Product::find($id);
        if (!$product) {
            throw new \Exception("Product not found.");
        }
        return $product;
    }

    public function update($id, array $data)
    {
        $this->validateData($data);

        $product = Product::find($id);
        if (!$product) {
            throw new \Exception("Product not found.");
        }
        $product->update($data);
        return $product;
    }

    public function delete($id)
    {
        $product = Product::find($id);
        if (!$product) {
            throw new \Exception("Product not found.");
        }
        return $product->delete();
    }

    protected function validateData(array $data)
    {
        $validator = Validator::make($data, [
            'nama' => 'required|string|max:255',
            'jumlah_stok' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
