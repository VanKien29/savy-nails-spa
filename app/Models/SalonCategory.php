<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class SalonCategory extends Model
{
    protected $guarded = ['id'];

    public function services(): HasMany
    {
        return $this->hasMany(SalonService::class, 'category_id')->where('active', true)->orderBy('sort_order');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(SalonImage::class, 'image_id');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('salon.catalog'));
        static::deleted(fn () => Cache::forget('salon.catalog'));
    }
}
