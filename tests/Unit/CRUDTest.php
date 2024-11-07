<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\CRUD;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class CRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $crudService;
    protected $product;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Inisialisasi CRUD service
        $this->crudService = new CRUD();

        // Buat pengguna admin
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Buat produk yang bisa digunakan di beberapa pengujian
        $this->product = Product::factory()->create([
            'nama' => 'Produk A',
            'jumlah_stok' => 10,
            'harga' => 10000
        ]);

        // Login sebagai admin
        Auth::login($this->admin);
    }

    public function test_create_product()
    {
        $data = [
            'nama' => 'Produk B',
            'jumlah_stok' => 20,
            'harga' => 15000
        ];

        $response = $this->crudService->create($data);
        $this->assertDatabaseHas('products', $data);
        $this->assertInstanceOf(Product::class, $response);
    }

    public function test_update_product()
    {
        $updatedData = [
            'nama' => 'Produk A Updated',
            'jumlah_stok' => 5,
            'harga' => 20000
        ];

        $response = $this->crudService->update($this->product->id, $updatedData);

        $this->assertEquals('Produk A Updated', $response->nama);
        $this->assertEquals(5, $response->jumlah_stok);
        $this->assertEquals(20000, $response->harga);
    }

    public function test_delete_product()
    {
        $this->crudService->delete($this->product->id);
        $this->assertDatabaseMissing('products', ['id' => $this->product->id]);
    }

    public function test_read_product_as_any_user()
    {
        // Baca produk yang dibuat di setUp
        $response = $this->crudService->read($this->product->id);

        // Verifikasi bahwa produk yang diambil sama dengan yang dibuat
        $this->assertEquals($this->product->id, $response->id);
        $this->assertEquals($this->product->nama, $response->nama);
        $this->assertEquals($this->product->jumlah_stok, $response->jumlah_stok);
        $this->assertEquals($this->product->harga, $response->harga);
    }
}
