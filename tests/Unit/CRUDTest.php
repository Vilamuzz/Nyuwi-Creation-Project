<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\CRUD;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;

class CRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $crudService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crudService = new CRUD();
    }

    public function test_create_product()
    {
        $data = [
            'nama' => 'Produk A',
            'jumlah_stok' => 10,
            'harga' => 15000
        ];

        $response = $this->crudService->create($data);
        $this->assertDatabaseHas('products', $data);
        $this->assertInstanceOf(Product::class, $response);
    }

    public function test_read_product()
    {
        $product = Product::factory()->create();
        $response = $this->crudService->read($product->id);

        $this->assertEquals($product->id, $response->id);
        $this->assertEquals($product->nama, $response->nama);
    }

    public function test_update_product()
    {
        $product = Product::factory()->create();
        $updatedData = ['nama' => 'Produk A Updated', 'jumlah_stok' => 5, 'harga' => 20000];

        $response = $this->crudService->update($product->id, $updatedData);

        $this->assertEquals('Produk A Updated', $response->nama);
        $this->assertEquals(5, $response->jumlah_stok);
        $this->assertEquals(20000, $response->harga);
    }

    public function test_delete_product()
    {
        $product = Product::factory()->create();
        $this->crudService->delete($product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_create_product_with_invalid_data()
    {
        $data = ['nama' => null]; // Assuming 'nama' is required
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->crudService->create($data);
    }

    public function test_update_nonexistent_product()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->crudService->update(999, ['nama' => 'Produk C']);
    }

    public function test_delete_nonexistent_product()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->crudService->delete(999);
    }
}
