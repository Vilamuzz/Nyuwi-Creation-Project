<?php

namespace Tests\Unit;

use App\Models\Product;
use App\CRUD;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $crudService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crudService = new CRUD();
    }

    public function testCreateProductSuccessfully()
    {
        $data = [
            'nama' => 'Product 1',
            'jumlah_stok' => 10,
            'harga' => 1000,
        ];

        $product = $this->crudService->create($data);

        $this->assertDatabaseHas('products', $data);
        $this->assertEquals('Product 1', $product->nama);
    }

    public function testReadProductSuccessfully()
    {
        $product = Product::factory()->create();

        $foundProduct = $this->crudService->read($product->id);
        $this->assertEquals($product->id, $foundProduct->id);
    }

    public function testUpdateProductSuccessfully()
    {
        $product = Product::factory()->create([
            'nama' => 'Product 1',
            'jumlah_stok' => 5,
            'harga' => 500,
        ]);

        $updatedData = [
            'nama' => 'Updated Product',
            'jumlah_stok' => 15,
            'harga' => 1500,
        ];

        $updatedProduct = $this->crudService->update($product->id, $updatedData);

        $this->assertEquals('Updated Product', $updatedProduct->nama);
        $this->assertDatabaseHas('products', $updatedData);
    }

    public function testDeleteProductSuccessfully()
    {
        $product = Product::factory()->create();

        $this->crudService->delete($product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function testValidationFailsWithInvalidData()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'nama' => '', // nama is required
            'jumlah_stok' => -1, // jumlah_stok cannot be negative
            'harga' => 'invalid_price', // harga must be numeric
        ];

        $this->crudService->create($data);
    }

    public function testHandleErrorWhenProductNotFound()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Product not found.");

        $this->crudService->read(999); // Assuming ID 999 does not exist
    }
}
