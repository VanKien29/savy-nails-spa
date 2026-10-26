<?php

namespace App\Models;

use App\Services\SalonContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        static::saved(function () {
            app(SalonContent::class)->forget();
        });
        static::deleted(function () {
            app(SalonContent::class)->forget();
        });
        static::saving(fn (self $category) => Validator::make($category->attributesToArray(), [
            'name' => 'required|string|max:160', 'slug' => ['required', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('salon_categories', 'slug')->ignore($category->id)],
            'description' => 'nullable|string|max:2000', 'image_id' => 'nullable|exists:salon_images,id', 'sort_order' => 'integer|min:0',
        ])->validate());
        static::deleting(function (self $category) {
            if (SalonService::where('category_id', $category->id)->exists()) {
                throw ValidationException::withMessages(['category' => 'Chuyển hoặc xóa các dịch vụ trong danh mục trước khi xóa danh mục.']);
            }
        });
    }
}
