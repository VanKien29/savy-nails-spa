<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Redirect extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saving(function (self $redirect) {
            Validator::make($redirect->attributesToArray(), [
                'source' => ['required', 'max:190', 'regex:~^/(?!/)[a-z0-9/-]*$~', 'different:target'],
                'target' => ['required', 'max:190', 'regex:~^/(?!/)[a-z0-9/-]*$~'],
            ])->validate();
            $target = $redirect->target;
            $visited = [$redirect->source];
            while ($target) {
                if (in_array($target, $visited, true) || count($visited) > 20) {
                    throw ValidationException::withMessages(['target' => 'Redirect tạo vòng lặp hoặc chuỗi quá dài.']);
                }
                $visited[] = $target;
                $target = self::where('source', $target)->value('target');
            }
        });
    }
}
