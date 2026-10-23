<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['variants' => 'array'];
    }

    public function url(): ?string
    {
        $variant = collect($this->variants ?? [])->sortByDesc('width')->first();

        return $variant ? Storage::disk($this->disk)->url($variant['path']) : null;
    }
}
