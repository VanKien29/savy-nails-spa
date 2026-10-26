<?php

namespace App\Models;

use App\Services\SalonContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SalonService extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['includes' => 'array', 'price' => 'decimal:2', 'gel_price' => 'decimal:2', 'fill_price' => 'decimal:2', 'price_from' => 'boolean', 'active' => 'boolean', 'featured' => 'boolean'];
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
        return $this->price !== null ? self::moneyLabel($this->price).($this->price_from ? '+' : '') : ($this->price_note ?: app(SalonContent::class)->all()['profile']['copy']['menu']['inquire_label']);
    }

    public static function moneyLabel(float|string $amount): string
    {
        return '$'.number_format((float) $amount, (float) $amount === floor((float) $amount) ? 0 : 2);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            app(SalonContent::class)->forget();
        });
        static::deleted(function () {
            app(SalonContent::class)->forget();
        });
        static::saving(function (self $service): void {
            if (blank($service->slug)) {
                $base = rtrim(substr(Str::slug($service->name) ?: 'service', 0, 180), '-');
                $slug = $base;
                while (self::where('slug', $slug)->whereKeyNot($service->id ?? 0)->exists()) {
                    $slug = $base.'-'.Str::lower(Str::random(6));
                }
                $service->slug = $slug;
            }
            Validator::make($service->attributesToArray(), [
                'name' => 'required|string|max:200', 'slug' => ['required', 'max:200', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('salon_services', 'slug')->ignore($service->id)],
                'category_id' => 'required|exists:salon_categories,id', 'image_id' => 'nullable|exists:salon_images,id',
                'description' => 'nullable|string|max:5000', 'group' => 'nullable|string|max:160',
                'price' => 'nullable|numeric|min:0|max:999999.99', 'gel_price' => 'nullable|numeric|min:0|max:999999.99', 'fill_price' => 'nullable|numeric|min:0|max:999999.99',
                'price_note' => 'nullable|string|max:255', 'duration_label' => 'nullable|string|max:255', 'massage_minutes' => 'nullable|integer|min:0|max:255',
                'includes_label' => 'nullable|string|max:160', 'sort_order' => 'integer|min:0', 'includes' => 'nullable|array|max:24', 'includes.*' => 'string|max:300',
            ])->validate();
        });
    }
}
