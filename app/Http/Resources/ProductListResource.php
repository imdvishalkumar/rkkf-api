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
            'details' => $this->details,
            'image1' => $makeImageUrl($this->image1),
            'image2' => $makeImageUrl($this->image2),
            'image3' => $makeImageUrl($this->image3),
            'belt_ids' => $this->belt_ids,
            'is_active' => (int) $this->active,
            'variation' => $firstVariation ? [
                'variation_id' => $firstVariation->id,
                'variation' => $firstVariation->variation,
                'price' => $firstVariation->price,
                'qty' => $firstVariation->qty,
            ] : null,
        ];
    }
}
