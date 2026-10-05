<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid {--hours=24 : Number of hours after which unpaid orders expire}';

    protected $description = 'Cancel unpaid orders past the payment window and restore their reserved stock';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $expiredCount = 0;

        $orders = Order::whereIn('payment_status', ['unpaid', 'pending'])
            ->whereNotIn('status', ['cancelled', 'completed', 'shipped'])
            ->where('created_at', '<=', $cutoff)
            ->get();

        foreach ($orders as $order) {
            DB::transaction(function () use ($order, &$expiredCount) {
                $order->refresh();

                if ($order->status === 'cancelled' || in_array($order->payment_status, ['paid', 'settlement'], true)) {
                    return;
                }

                $order->update([
                    'payment_status' => 'expired',
                    'status' => 'cancelled',
                ]);

                $order->restoreStock();
                $expiredCount++;
            });
        }

        $this->info("Expired {$expiredCount} unpaid orders and restored their stock.");

        return Command::SUCCESS;
    }
}
