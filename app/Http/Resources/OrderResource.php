<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Assuming Product relationship exists and provides image/details
        $product = $this->product;

        $baseUrl = url('images/products') . '/';
        $placeholder = $baseUrl . 'placeholder.png';

        // Resolve image name (Product model uses image1, fallback to image just in case)
        $imageName = $product ? ($product->image1 ?? $product->image ?? null) : null;
        $fullImageUrl = $imageName ? $baseUrl . $imageName : $placeholder;

        // Status formatting
        $statusLabel = 'Pending';
        if ($this->flag_delivered) {
            $statusLabel = 'Delivered';
        } elseif ($this->status) {
            $statusLabel = 'Confirmed';
        }

        return [
            'order_id' => $this->order_id,
            'order_no' => '#' . $this->order_id,
            'status' => $statusLabel,
            'date' => optional($this->date)->format('d M Y'),
            'total_amount' => $this->p_price,
            'product' => [
                'id' => $product ? $product->product_id : null,
                'name' => $product ? $product->name : 'Unknown Product',
                'image' => $fullImageUrl,
                'price' => $this->p_price,
                'qty' => $this->qty,
                'size' => $this->name_var ?? null, // UI shows size, mapping to name_var
            ],
        ];
    }
}
