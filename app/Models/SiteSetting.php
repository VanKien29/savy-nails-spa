<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }
}
