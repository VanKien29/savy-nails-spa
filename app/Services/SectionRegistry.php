<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SectionRegistry
{
    public const TYPES = ['hero' => 'Hero', 'about' => 'Giới thiệu', 'features' => 'Đặc điểm', 'services' => 'Dịch vụ', 'benefits' => 'Lợi ích', 'products' => 'Sản phẩm', 'gallery' => 'Thư viện ảnh', 'logos' => 'Đối tác', 'testimonials' => 'Nhận xét (demo)', 'statistics' => 'Thống kê', 'process' => 'Quy trình', 'pricing' => 'Bảng giá', 'faq' => 'Câu hỏi', 'cta' => 'Kêu gọi hành động', 'contact' => 'Liên hệ', 'map' => 'Địa chỉ'];

    public function validate(array $sections): array
    {
        return Validator::make(['sections' => $sections], [
            'sections' => ['array', 'max:30'],
            'sections.*' => ['array:type,variant,ordering,enabled,id,eyebrow,heading,body,button_label,button_url,media_id,items'],
            'sections.*.type' => ['required', Rule::in(array_keys(self::TYPES))],
            'sections.*.variant' => ['required', Rule::in(['default', 'centered', 'split', 'background'])],
            'sections.*.ordering' => ['sometimes', 'integer', 'min:0'], 'sections.*.enabled' => ['required', 'boolean'],
            'sections.*.id' => ['nullable', 'regex:/^[a-z][a-z0-9-]{0,60}$/'],
            'sections.*.eyebrow' => ['nullable', 'string', 'max:120'], 'sections.*.heading' => ['nullable', 'string', 'max:240'],
            'sections.*.body' => ['nullable', 'string', 'max:4000'],
            'sections.*.button_label' => ['nullable', 'string', 'max:80'],
            'sections.*.button_url' => ['nullable', 'regex:~^(?:/(?!/)[^\s]*|#[a-zA-Z][\w-]*|https://[^\s]+)$~'],
            'sections.*.media_id' => ['nullable', 'exists:media,id'], 'sections.*.items' => ['nullable', 'array', 'max:24'],
            'sections.*.items.*' => ['array:title,text,label,value,media_id'],
            'sections.*.items.*.title' => ['nullable', 'string', 'max:180'], 'sections.*.items.*.text' => ['nullable', 'string', 'max:2000'],
            'sections.*.items.*.label' => ['nullable', 'string', 'max:100'], 'sections.*.items.*.value' => ['nullable', 'string', 'max:60'],
            'sections.*.items.*.media_id' => ['nullable', 'exists:media,id'],
        ])->validate()['sections'];
    }
}
