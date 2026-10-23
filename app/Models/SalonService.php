<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SalonService extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['includes' => 'array', 'price' => 'decimal:2', 'gel_price' => 'decimal:2', 'price_from' => 'boolean', 'active' => 'boolean', 'featured' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SalonCategory::class, 'category_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(SalonImage::class, 'image_id');
    }

    public function priceLabel(): string
    {
        return $this->price !== null ? '$'.number_format((float) $this->price, 0).($this->price_from ? '+' : '') : ($this->price_note ?: 'Please inquire');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('salon.catalog'));
        static::deleted(fn () => Cache::forget('salon.catalog'));
        static::saving(fn (self $service) => Validator::make($service->attributesToArray(), ['name' => 'required|string|max:200', 'price' => 'nullable|numeric|min:0', 'gel_price' => 'nullable|numeric|min:0', 'includes' => 'nullable|array', 'includes.*' => 'string|max:300'])->validate());
    }
}
