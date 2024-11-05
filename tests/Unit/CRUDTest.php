<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
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

    public function testCreateReadUpdateDeleteProductSuccessfully()
    {
        $data = [
            'nama' => 'Product 1',
            'jumlah_stok' => 10,
            'harga' => 1000,
        ];

        // Test Create
        $product = $this->crudService->create($data);
        $this->assertDatabaseHas('products', $data);

        // Test Read
        $foundProduct = $this->crudService->read($product->id);
        $this->assertEquals($product->id, $foundProduct->id);

        // Test Update
        $updatedData = [
            'nama' => 'Updated Product',
            'jumlah_stok' => 15,
            'harga' => 1500,
        ];
        $updatedProduct = $this->crudService->update($product->id, $updatedData);
        $this->assertDatabaseHas('products', $updatedData);

        // Test Delete
        $this->crudService->delete($product->id);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function testCreateProductWithInvalidData()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'nama' => '', // nama is required
            'jumlah_stok' => -1, // jumlah_stok cannot be negative
            'harga' => 'invalid_price', // harga must be numeric
        ];

        $this->crudService->create($data);
    }

    public function testUpdateNonExistentProduct()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Product not found.");

        $this->crudService->update(999, [
            'nama' => 'Non-existent product',
            'jumlah_stok' => 5,
            'harga' => 500,
        ]);
    }

    public function testDeleteProductUsedByAnotherFeature()
    {
        // This would be specific to your application. Here, simulate an error if deletion is attempted
        $product = Product::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Product is in use and cannot be deleted.");

        $this->crudService->delete($product->id);
    }

    public function testCRUDOperationsWithoutProperPermissions()
    {
        // Assuming you have a permission system in place
        $this->actingAs(User::factory()->create(['role' => 'user'])); // User without proper permissions

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Unauthorized.");

        $this->crudService->create(['nama' => 'Unauthorized Product', 'jumlah_stok' => 10, 'harga' => 1000]);
    }
}
