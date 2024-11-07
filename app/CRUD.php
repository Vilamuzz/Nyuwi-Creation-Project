<?php

namespace App;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class CRUD
{
    public function create(array $data)
    {
        $this->authorizeAdmin();

        if (empty($data) || array_filter($data, fn($value) => is_null($value))) {
            throw new InvalidArgumentException('Data cannot be null');
        }

        return Product::create($data);
    }

    public function read($id)
    {
        return Product::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $this->authorizeAdmin();

        $product = Product::findOrFail($id);
        $product->update($data);

        return $product;
    }

    public function delete($id)
    {
        $this->authorizeAdmin();

        $product = Product::findOrFail($id);
        $product->delete();

        return true;
    }

    protected function authorizeAdmin()
    {
        $user = Auth::user();

        if ($user->role !== 'Admin') {
            throw new \Exception('Unauthorized: Only admins can perform this action');
        }
    }
}
