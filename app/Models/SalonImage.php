<?php

namespace App\Models;

use App\Services\SalonContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalonImage extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['variants' => 'array', 'is_inspiration' => 'boolean', 'show_in_gallery' => 'boolean'];
    }

    public function url(): string
    {
        if ($this->media_id) {
            return $this->media?->url() ?? '';
        }
        $variant = collect($this->variants)->sortByDesc('width')->first();

        return $variant ? '/assets/optimized/'.$variant['file'] : '';
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function displayVariants(): array
    {
        if ($this->media_id) {
            return collect($this->media?->variants ?? [])->map(fn ($v) => ['url' => Storage::disk($this->media->disk)->url($v['path']), 'width' => $v['width'], 'height' => $v['height']])->all();
        }

        return collect($this->variants ?? [])->filter(fn ($v) => str_ends_with($v['file'], '.webp'))->map(fn ($v) => ['url' => asset('assets/optimized/'.$v['file']), 'width' => $v['width'], 'height' => $v['height']])->all();
    }

    public function inUse(): bool
    {
        if (SalonCategory::where('image_id', $this->id)->exists() || SalonService::where('image_id', $this->id)->exists()) {
            return true;
        }
        $references = [SalonProfile::find(1)?->data ?? [], ...Page::pluck('sections')->all(), ...PageRevision::pluck('snapshot')->all()];
        $referenceKey = $this->getOriginal('key') ?? $this->key;
        $contains = function (array $values) use (&$contains, $referenceKey): bool {
            foreach ($values as $value) {
                if (is_array($value) ? $contains($value) : $value === $referenceKey) {
                    return true;
                }
            }

            return false;
        };

        return $contains($references);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            app(SalonContent::class)->forget();
        });
        static::deleted(function () {
            app(SalonContent::class)->forget();
        });
        static::saving(function (self $image) {
            Validator::make($image->attributesToArray(), ['key' => ['required', 'regex:/^[a-z0-9-]+$/', 'max:100', Rule::unique('salon_images', 'key')->ignore($image->id)], 'alt' => 'required|string|max:255', 'caption' => 'required|string|max:255', 'category' => ['required', 'regex:/^[a-z0-9-]+$/', 'max:80'], 'media_id' => 'nullable|exists:media,id', 'source_url' => 'nullable|url:https|max:1000', 'source_credit' => 'nullable|string|max:255'])->validate();
            if ($image->exists && $image->isDirty('key') && $image->inUse()) {
                throw ValidationException::withMessages(['key' => 'Không đổi mã ảnh đang được sử dụng.']);
            }
            if ($image->media_id && $image->media?->status !== 'ready') {
                throw ValidationException::withMessages(['media_id' => 'Chỉ chọn ảnh đã xử lý xong.']);
            }
            foreach ($image->variants ?? [] as $variant) {
                if (! preg_match('~^the-k/[a-z0-9.-]+-[0-9]+\.(webp|avif)$~', $variant['file'] ?? '')) {
                    throw new \InvalidArgumentException('Invalid public image variant.');
                }
            }
        });
        static::deleting(function (self $image) {
            if ($image->inUse()) {
                throw ValidationException::withMessages(['image' => 'Ảnh đang dùng trên website. Hãy thay ảnh ở các vị trí liên quan trước khi xóa.']);
            }
        });
    }
}
