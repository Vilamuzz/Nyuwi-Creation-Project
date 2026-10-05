<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderCompleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $orderId = $this->input('order_id');
        if (! $orderId) {
            return true;
        }

        $order = Order::find($orderId);
        if (! $order) {
            return true;
        }

        return (int) $order->user_id === (int) $this->user()?->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'reviews' => ['required', 'array', 'min:1'],
            'reviews.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'reviews.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $orderId = $this->input('order_id');
            if (! $orderId) {
                return;
            }

            $order = Order::with('orderItems')->find($orderId);
            if (! $order || (int) $order->user_id !== (int) $this->user()?->id) {
                return;
            }

            if (! in_array($order->status, ['shipped', 'completed'], true) || ! in_array($order->payment_status, ['paid', 'settlement'], true)) {
                $validator->errors()->add('order_id', 'Pesanan belum dapat diselesaikan atau belum dalam status pengiriman/selesai.');
            }

            $orderProductIds = $order->orderItems->pluck('product_id')->all();
            $reviews = $this->input('reviews', []);

            if (is_array($reviews)) {
                foreach ($reviews as $index => $review) {
                    $productId = $review['product_id'] ?? null;
                    if ($productId && ! in_array($productId, $orderProductIds)) {
                        $validator->errors()->add("reviews.{$index}.product_id", 'Produk tidak ditemukan dalam pesanan ini.');
                    }
                }
            }
        });
    }
}

