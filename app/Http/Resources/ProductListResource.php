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
        $r = is_array($this->resource) ? (object) $this->resource : $this->resource;

        $baseUrl = rtrim(config('app.url'), '/') . '/images/products/';
        $placeholder = $baseUrl . 'placeholder.png';

        $makeImageUrl = function ($file) use ($baseUrl, $placeholder) {
            if (empty($file)) {
                return $placeholder;
            }

            // Ensure we only use the filename portion
            $basename = basename($file);
            $ext = pathinfo($basename, PATHINFO_EXTENSION);
            $name = pathinfo($basename, PATHINFO_FILENAME);

            // Slugify the filename (remove spaces/special chars)
            $safe = Str::slug($name);

            $safeFilename = $safe . ($ext ? '.' . $ext : '');

            return $baseUrl . $safeFilename;
        };

        return [
            'product_id' => $r->product_id ?? null,
            'name' => $r->name ?? null,
            'details' => $r->details ?? null,
            'image1' => $makeImageUrl($r->image1),
            'image2' => $makeImageUrl($r->image2),
            'image3' => $makeImageUrl($r->image3),
            'belt_ids' => $r->belt_ids ?? null,
            'is_active' => isset($r->is_active) ? (int) $r->is_active : null,
            'variation' => [
                'variation_id' => $r->id ?? $r->variation_id ?? null,
                'variation' => $r->variation ?? null,
                'price' => $r->price ?? null,
                'qty' => $r->qty ?? null,
            ],
        ];
    }
}
