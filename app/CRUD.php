<?php

namespace App;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class CRUD
{
    public function create(array $data)
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException('Data cannot be null');
        }

        // Daftar field yang wajib ada
        $requiredFields = ['nama', 'jumlah_stok', 'harga'];

        // Periksa jika field wajib tidak ada di $data atau bernilai null
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data) || is_null($data[$field])) {
                throw new \InvalidArgumentException("Field '{$field}' is required and cannot be null");
            }
        }

        return Product::create($data);
    }



    public function read($id)
    {
        try {
            return Product::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            // Menangani kasus ketika produk tidak ditemukan
            throw new Exception("Product with ID {$id} not found", 404);
        }
    }


    public function update($id, array $data)
    {
        try {
            // Cari produk berdasarkan ID, lempar ModelNotFoundException jika tidak ditemukan
            $product = Product::findOrFail($id);

            // Daftar field yang wajib ada untuk pembaruan
            $requiredFields = ['nama', 'jumlah_stok', 'harga'];

            // Periksa jika field wajib tidak ada di $data atau bernilai null
            foreach ($requiredFields as $field) {
                if (array_key_exists($field, $data) && is_null($data[$field])) {
                    throw new \InvalidArgumentException("Field '{$field}' cannot be null");
                }
            }

            // Lakukan pembaruan data produk
            $product->update($data);
            return $product;
        } catch (ModelNotFoundException $e) {
            throw new Exception("Product with ID {$id} not found", 404);
        }
    }


    public function delete($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return true;
        } catch (ModelNotFoundException $e) {
            throw new Exception("Product with ID {$id} not found");
        }
    }
}
