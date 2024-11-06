<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\CRUD;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $crudService;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Inisialisasi CRUD service
        $this->crudService = new CRUD();

        // Buat produk yang bisa digunakan di beberapa pengujian
        $this->product = Product::factory()->create([
            'nama' => 'Produk A',
            'jumlah_stok' => 10,
            'harga' => 10000
        ]);
    }

    public function test_create_product()
    {
        // Kasus ketika data valid
        $data = [
            'nama' => 'Produk B',
            'jumlah_stok' => 20,
            'harga' => 15000
        ];

        $response = $this->crudService->create($data);
        $this->assertDatabaseHas('products', $data);
        $this->assertInstanceOf(Product::class, $response);

        // Kasus ketika data tidak valid (misalnya data null)
        try {
            $this->crudService->create($data);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Data cannot be null', $e->getMessage());
        }
    }

    public function test_read_product()
    {
        // Baca produk yang dibuat di setUp
        $response = $this->crudService->read($this->product->id);

        // Verifikasi bahwa produk yang diambil sama dengan yang dibuat
        $this->assertEquals($this->product->id, $response->id);
        $this->assertEquals($this->product->nama, $response->nama);
        $this->assertEquals($this->product->jumlah_stok, $response->jumlah_stok);
        $this->assertEquals($this->product->harga, $response->harga);
    }

    public function test_update_product()
    {
        // Data yang diubah untuk produk
        $updatedData = [
            'nama' => 'Produk A Updated',
            'jumlah_stok' => 5,
            'harga' => 20000
        ];

        $response = $this->crudService->update($this->product->id, $updatedData);

        // Verifikasi perubahan data
        $this->assertEquals('Produk A Updated', $response->nama);
        $this->assertEquals(5, $response->jumlah_stok);
        $this->assertEquals(20000, $response->harga);
    }

    public function test_delete_product()
    {
        // Hapus produk
        $this->crudService->delete($this->product->id);

        // Pastikan produk telah terhapus dari database
        $this->assertDatabaseMissing('products', ['id' => $this->product->id]);
    }
}
