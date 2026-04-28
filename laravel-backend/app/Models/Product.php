<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'price', 'sale_percent', 'description', 'category',
        'available', 'is_active', 'featured', 'rating', 'reviews',
        'has_sizes', 'sizes', 'variant_label', 'admin_note',
        'image', 'image_name', 'image_size', 'image_type',
        'image_2', 'image_2_name', 'image_2_size', 'image_2_type',
        'image_3', 'image_3_name', 'image_3_size', 'image_3_type',
        'image_4', 'image_4_name', 'image_4_size', 'image_4_type',
        'image_5', 'image_5_name', 'image_5_size', 'image_5_type',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_percent' => 'decimal:2',
            'available' => 'boolean',
            'is_active' => 'boolean',
            'featured' => 'boolean',
            'has_sizes' => 'boolean',
            'sizes' => 'array',
            'rating' => 'decimal:1',
        ];
    }

    /**
     * Effective price after sale_percent is applied. If no sale, equals price.
     */
    public function getSalePriceAttribute(): float
    {
        $pct = (float) ($this->sale_percent ?? 0);
        if ($pct <= 0) {
            return (float) $this->price;
        }
        return round((float) $this->price * (1 - $pct / 100), 2);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return ((float) ($this->sale_percent ?? 0)) > 0;
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get base64 data URL for an image slot (1-5).
     */
    public function getImageDataUrl(int $slot = 1): ?string
    {
        $field = $slot === 1 ? 'image' : "image_{$slot}";
        $typeField = $slot === 1 ? 'image_type' : "image_{$slot}_type";

        $data = $this->getRawOriginal($field) ?? $this->getAttribute($field);
        if (empty($data)) {
            return null;
        }

        $mime = $this->getAttribute($typeField) ?: 'image/jpeg';
        return "data:{$mime};base64," . base64_encode($data);
    }

    /**
     * Get all image data URLs as an array.
     */
    public function getAllImageUrls(): array
    {
        $images = [];
        for ($i = 1; $i <= 5; $i++) {
            $url = $this->getImageDataUrl($i);
            if ($url) {
                $images[] = $url;
            }
        }
        return $images;
    }
}
