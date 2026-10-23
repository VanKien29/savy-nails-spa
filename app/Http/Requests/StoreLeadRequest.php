<?php

namespace App\Http\Requests;

use App\Services\SiteSettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! config('starter.preview') && app(SiteSettingsService::class)->get('features.contact');
    }

    public function rules(): array
    {
        $rules = [
            'full_name' => 'required|string|min:2|max:120',
            'phone' => ['nullable', 'required_without:email', 'regex:/^\+?[0-9 ()-]{8,24}$/'],
            'email' => 'nullable|required_without:phone|email:rfc|max:160',
            'message' => 'nullable|string|max:4000', 'page_id' => 'required|integer|exists:pages,id',
            'consent' => config('starter.leads.consent') ? 'accepted' : 'nullable|boolean',
            'website' => 'nullable|max:0',
            'form_token' => ['required', function ($attribute, $value, $fail) {
                try {
                    $issued = (int) Crypt::decryptString($value);
                    if (time() - $issued < 2 || time() - $issued > 7200) {
                        $fail(__('starter.form_expired'));
                    }
                } catch (\Throwable) {
                    $fail(__('starter.form_invalid'));
                }
            }],
            'utm' => 'nullable|array:source,medium,campaign,term,content', 'utm.*' => 'nullable|string|max:200',
            'extra' => 'nullable|array:'.implode(',', array_keys(config('starter.leads.extra_fields'))),
        ];
        foreach (config('starter.leads.extra_fields') as $name => $field) {
            $rules['extra.'.$name] = $field['rules'];
        }

        return $rules;
    }
}
