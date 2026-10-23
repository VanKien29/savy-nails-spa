<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NavigationItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(fn (self $item) => Validator::make($item->attributesToArray(), [
            'label' => 'required|string|max:80', 'url' => ['required', 'max:500', 'regex:~^(?:/(?!/)[^\s]*|#[a-zA-Z][\w-]*|https://[^\s]+|mailto:[^\s]+|tel:\+?[0-9 -]+)$~'],
            'location' => ['required', Rule::in(['header', 'footer'])], 'sort_order' => 'integer|min:0',
        ])->validate());
    }
}
