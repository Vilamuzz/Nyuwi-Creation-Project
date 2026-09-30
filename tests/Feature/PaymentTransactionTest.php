<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTransactionTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(): Order
    {
        $user = User::factory()->create();

        return Order::create([
            'user_id' => $user->id,
            'recipient_name' => 'John Doe',
            'address' => 'Jl. Merdeka No. 1',
            'province_id' => '6',
            'province_name' => 'Jawa Barat',
            'city_id' => '22',
            'city_name' => 'Bandung',
            'district_id' => '220108',
            'district_name' => 'Coblong',
            'postal_code' => '40135',
            'phone' => '081234567890',
            'email' => 'john@example.com',
            'courier' => 'jne',
            'shipping_service' => 'REG',
            'weight' => 1000,
            'shipping_cost' => 25000,
            'subtotal' => 100000,
            'total_price' => 125000,
            'payment_status' => 'unpaid',
            'status' => 'waiting',
        ]);
    }

    public function test_order_has_default_payment_status_and_fulfillment_status(): void
    {
        $order = $this->createOrder();

        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertEquals('waiting', $order->status);
        $this->assertNotEmpty($order->order_number);
    }

    public function test_can_create_payment_transaction_linked_to_order(): void
    {
        $order = $this->createOrder();

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_transaction_id' => 'midtrans-trx-12345',
            'payment_channel' => 'qris',
            'amount' => 125000,
            'raw_status' => 'settlement',
            'expired_at' => now()->addDay(),
            'paid_at' => now(),
            'raw_payload' => ['transaction_id' => 'midtrans-trx-12345', 'transaction_status' => 'settlement'],
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transaction->id,
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_transaction_id' => 'midtrans-trx-12345',
            'payment_channel' => 'qris',
            'raw_status' => 'settlement',
        ]);

        $this->assertInstanceOf(Order::class, $transaction->order);
        $this->assertEquals($order->id, $transaction->order->id);

        $this->assertCount(1, $order->paymentTransactions);
        $this->assertEquals($transaction->id, $order->paymentTransactions->first()->id);
        $this->assertIsArray($transaction->raw_payload);
        $this->assertEquals('midtrans-trx-12345', $transaction->raw_payload['transaction_id']);
    }

    public function test_deleting_order_cascades_to_payment_transactions(): void
    {
        $order = $this->createOrder();

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_transaction_id' => 'midtrans-cascade-test',
            'payment_channel' => 'qris',
            'amount' => 125000,
        ]);

        $this->assertDatabaseCount('payment_transactions', 1);

        $order->delete();

        $this->assertDatabaseCount('payment_transactions', 0);
    }
}
