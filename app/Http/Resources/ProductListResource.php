<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $baseUrl = url('images/products') . '/';
        $placeholder = $baseUrl . 'placeholder.png';

        $makeImageUrl = function ($file) use ($baseUrl, $placeholder) {
            if (empty($file)) {
                return $placeholder;
            }
            // Just return the base URL + filename, avoiding complex slugification
            return $baseUrl . basename($file);
        };

        // Get the first variation if variations relationship is loaded
        $firstVariation = $this->variations->first();

        return [
            'product_id' => $this->product_id,
            'name' => $this->name,
            // 'details' => $this->details,
            'image1' => $makeImageUrl($this->image1),
            'share_product_link' => url('product/' . $this->product_id),
            'price' => $firstVariation ? $firstVariation->price : null,
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,
        ];
    }
}
