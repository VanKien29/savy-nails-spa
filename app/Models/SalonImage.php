<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SalonImage extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['variants' => 'array', 'is_inspiration' => 'boolean', 'show_in_gallery' => 'boolean'];
    }

    public function url(): string
    {
        $variant = collect($this->variants)->sortByDesc('width')->first();

        return '/assets/optimized/'.$variant['file'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('salon.catalog'));
        static::deleted(fn () => Cache::forget('salon.catalog'));
        static::saving(function (self $image) {
            Validator::make($image->attributesToArray(), ['source_url' => 'nullable|url:https|max:1000', 'source_credit' => 'nullable|string|max:255'])->validate();
            foreach ($image->variants ?? [] as $variant) {
                if (! preg_match('~^the-k/[a-z0-9.-]+-[0-9]+\.(webp|avif)$~', $variant['file'] ?? '')) {
                    throw new \InvalidArgumentException('Invalid public image variant.');
                }
            }
        });
    }
}
