<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Lead extends Model
{
    public const STATUSES = ['new' => 'Mới', 'contacted' => 'Đã liên hệ', 'qualified' => 'Tiềm năng', 'completed' => 'Hoàn thành', 'spam' => 'Spam'];

    protected $guarded = ['id'];

    protected $hidden = ['fingerprint'];

    protected static function booted(): void
    {
        static::saving(fn (self $lead) => Validator::make(
            ['status' => $lead->status ?? 'new'], ['status' => ['required', Rule::in(array_keys(self::STATUSES))]]
        )->validate());
    }

    protected function casts(): array
    {
        return ['utm' => 'array', 'extra' => 'array', 'consent' => 'boolean'];
    }
}
