<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $serverKey = 'test-server-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.midtrans.server_key' => $this->serverKey]);
        config(['services.midtrans.client_key' => 'test-client-key']);
    }

    private function createTestOrder(?User $user = null): Order
    {
        $user = $user ?? User::factory()->create(['role' => 'pelanggan']);
        $category = Category::create(['name' => 'Flowers']);

        $product = Product::create([
            'name' => 'Rose Bouquet',
            'slug' => 'rose-bouquet-' . uniqid(),
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 100000,
            'weight' => 200,
            'description' => 'Test bouquet',
            'images' => ['rose.jpg'],
            'sizes' => [],
            'colors' => [],
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'name' => 'Customer Tester',
            'address' => 'Jl. Test No. 123',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'village' => 'Dago',
            'province' => 'Jawa Barat',
            'phone' => '08123456789',
            'email' => 'customer@test.com',
            'total_price' => 115000,
            'payment_method' => 'qris',
            'shipping_method' => 'JNE',
            'payment_status' => 'pending',
            'status' => 'processing',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100000,
            'total_price' => 100000,
        ]);

        return $order;
    }

    private function generateSignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
    }

    public function test_midtrans_service_creates_snap_token_with_valid_payload(): void
    {
        Http::fake([
            '*' => Http::response([
                'token' => 'mock-snap-token-12345',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-12345',
            ], 201),
        ]);

        $order = $this->createTestOrder();
        $service = new MidtransService($this->serverKey);

        $result = $service->createSnapToken($order);

        $this->assertEquals('mock-snap-token-12345', $result['token']);
        $this->assertNotEmpty($result['redirect_url']);
        $this->assertStringStartsWith('ORD-' . $order->id, $result['order_custom_id']);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $order = $this->createTestOrder();
        $customOrderId = 'ORD-' . $order->id . '-123456';

        $response = $this->postJson(route('webhooks.midtrans'), [
            'order_id' => $customOrderId,
            'status_code' => '200',
            'gross_amount' => '115000.00',
            'signature_key' => 'invalid-signature-hash',
            'transaction_id' => 'trx-mock-123',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Invalid signature']);
    }

    public function test_webhook_settlement_updates_order_to_paid_and_logs_transaction(): void
    {
        $order = $this->createTestOrder();
        $customOrderId = 'ORD-' . $order->id . '-123456';
        $grossAmount = '115000.00';
        $signature = $this->generateSignature($customOrderId, '200', $grossAmount);

        $response = $this->postJson(route('webhooks.midtrans'), [
            'order_id' => $customOrderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_id' => 'trx-mock-settle-01',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ]);

        $response->assertOk()
            ->assertJson(['payment_status' => 'paid']);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_transaction_id' => 'trx-mock-settle-01',
            'payment_channel' => 'qris',
            'raw_status' => 'settlement',
        ]);
    }

    public function test_webhook_expire_updates_order_to_expired(): void
    {
        $order = $this->createTestOrder();
        $customOrderId = 'ORD-' . $order->id . '-123456';
        $grossAmount = '115000.00';
        $signature = $this->generateSignature($customOrderId, '200', $grossAmount);

        $response = $this->postJson(route('webhooks.midtrans'), [
            'order_id' => $customOrderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_id' => 'trx-mock-expire-01',
            'transaction_status' => 'expire',
            'payment_type' => 'bank_transfer',
        ]);

        $response->assertOk()
            ->assertJson(['payment_status' => 'expired']);

        $order->refresh();
        $this->assertEquals('expired', $order->payment_status);
    }

    public function test_webhook_is_idempotent(): void
    {
        $order = $this->createTestOrder();
        $customOrderId = 'ORD-' . $order->id . '-123456';
        $grossAmount = '115000.00';
        $signature = $this->generateSignature($customOrderId, '200', $grossAmount);

        $payload = [
            'order_id' => $customOrderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_id' => 'trx-mock-idempotent-01',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ];

        // First call
        $firstResponse = $this->postJson(route('webhooks.midtrans'), $payload);
        $firstResponse->assertOk();

        $this->assertDatabaseCount('payment_transactions', 1);

        // Second duplicate call
        $secondResponse = $this->postJson(route('webhooks.midtrans'), $payload);
        $secondResponse->assertOk()
            ->assertJson(['message' => 'Notification already processed.']);

        // No duplicate row inserted
        $this->assertDatabaseCount('payment_transactions', 1);
    }

    public function test_customer_can_retrieve_payment_token_for_their_pending_order(): void
    {
        Http::fake([
            '*' => Http::response([
                'token' => 'snap-token-user-order-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/token-123',
            ], 201),
        ]);

        $user = User::factory()->create(['role' => 'pelanggan']);
        $order = $this->createTestOrder($user);

        $response = $this->actingAs($user)
            ->getJson(route('customer.orders.payment-token', $order->id));

        $response->assertOk()
            ->assertJson([
                'snap_token' => 'snap-token-user-order-123',
            ]);
    }

    public function test_customer_cannot_retrieve_payment_token_for_another_users_order(): void
    {
        $owner = User::factory()->create(['role' => 'pelanggan']);
        $anotherUser = User::factory()->create(['role' => 'pelanggan']);
        $order = $this->createTestOrder($owner);

        $response = $this->actingAs($anotherUser)
            ->getJson(route('customer.orders.payment-token', $order->id));

        $response->assertForbidden();
    }
}
