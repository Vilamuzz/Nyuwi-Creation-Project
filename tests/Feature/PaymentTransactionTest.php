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
            'name' => 'John Doe',
            'address' => 'Jl. Merdeka No. 1',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'village' => 'Dago',
            'province' => 'Jawa Barat',
            'phone' => '081234567890',
            'email' => 'john@example.com',
            'total_price' => 125000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'status' => 'processing',
        ]);
    }

    public function test_order_has_default_payment_status_and_fulfillment_status(): void
    {
        $order = $this->createOrder();

        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('processing', $order->status);
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
