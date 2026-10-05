<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentService;
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
            'recipient_name' => 'Customer Tester',
            'address' => 'Jl. Test No. 123',
            'province_id' => '6',
            'province_name' => 'Jawa Barat',
            'city_id' => '22',
            'city_name' => 'Bandung',
            'district_id' => '220108',
            'district_name' => 'Coblong',
            'postal_code' => '40135',
            'phone' => '08123456789',
            'email' => 'customer@test.com',
            'courier' => 'jne',
            'shipping_service' => 'REG',
            'weight' => 200,
            'shipping_cost' => 15000,
            'subtotal' => 100000,
            'total_price' => 115000,
            'payment_status' => 'unpaid',
            'status' => 'waiting',
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

    public function test_payment_service_reuses_existing_active_snap_token(): void
    {
        $order = $this->createTestOrder();

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_order_id' => 'ORD-' . $order->id . '-1-mock',
            'snap_token' => 'existing-active-snap-token',
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/existing',
            'amount' => 115000,
            'status' => 'pending',
            'expired_at' => now()->addHours(24),
        ]);

        $service = app(PaymentService::class);
        $result = $service->createSnapToken($order);

        $this->assertEquals('existing-active-snap-token', $result['token']);
        $this->assertEquals($transaction->id, $result['transaction_id']);
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
        $customOrderId = 'ORD-' . $order->id . '-999-1700000000';

        $tx = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_order_id' => $customOrderId,
            'snap_token' => 'test-snap-token',
            'amount' => 115000,
            'status' => 'pending',
        ]);

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
            ->assertJson(['message' => 'OK']);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $tx->id,
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_transaction_id' => 'trx-mock-settle-01',
            'payment_channel' => 'qris',
            'status' => 'settlement',
        ]);
    }

    public function test_webhook_expire_updates_order_to_expired(): void
    {
        $order = $this->createTestOrder();
        $product = $order->orderItems->first()->product;
        $product->update(['stock' => 9]); // Simulate stock reserved at checkout

        $customOrderId = 'ORD-' . $order->id . '-888-1700000000';

        $tx = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_order_id' => $customOrderId,
            'snap_token' => 'test-expire-token',
            'amount' => 115000,
            'status' => 'pending',
        ]);

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
            ->assertJson(['message' => 'OK']);

        $order->refresh();
        $this->assertEquals('expired', $order->payment_status);
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals(10, $product->fresh()->stock);
    }

    public function test_webhook_is_idempotent(): void
    {
        $order = $this->createTestOrder();
        $customOrderId = 'ORD-' . $order->id . '-777-1700000000';

        $tx = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_order_id' => $customOrderId,
            'amount' => 115000,
            'status' => 'pending',
        ]);

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

        // Second duplicate call
        $secondResponse = $this->postJson(route('webhooks.midtrans'), $payload);
        $secondResponse->assertOk()
            ->assertJson(['message' => 'Already processed']);
    }

    public function test_customer_can_retrieve_payment_token_for_their_pending_order(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $order = $this->createTestOrder($user);

        // Pre-seed an active pending snap_token
        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_order_id' => 'ORD-' . $order->id . '-1',
            'snap_token' => 'snap-token-user-order-123',
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/token-123',
            'amount' => 115000,
            'status' => 'pending',
            'expired_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('orders.payment', $order->id));

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
            ->postJson(route('orders.payment', $order->id));

        $response->assertForbidden();
    }
}
