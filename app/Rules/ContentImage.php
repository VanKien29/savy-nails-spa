<?php

namespace App\Rules;

use App\Models\Media;
use App\Models\SalonImage;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ContentImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value)) {
            if (SalonImage::where('key', $value)->exists()) {
                return;
            }
            if (preg_match('/^uploaded-media-([0-9]+)$/', $value, $match) && Media::whereKey($match[1])->where('status', 'ready')->exists()) {
                return;
            }
        }
        $fail('Vui lòng chọn ảnh có trong thư viện.');
    }
}
