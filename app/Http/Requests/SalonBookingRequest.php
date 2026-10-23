<?php

namespace App\Http\Requests;

use App\Models\SalonService;
use Illuminate\Validation\Rule;

class SalonBookingRequest extends StoreLeadRequest
{
    protected function prepareForValidation(): void
    {
        $service = is_scalar($this->input('extra.service_id')) ? SalonService::where('active', true)->find($this->input('extra.service_id')) : null;
        $notes = is_string($this->input('notes')) ? mb_substr($this->input('notes'), 0, 2500) : '';
        $date = is_string($this->input('extra.date')) ? $this->input('extra.date') : '';
        $time = is_string($this->input('extra.time')) ? $this->input('extra.time') : '';
        $this->merge(['message' => 'Appointment request: '.($service?->name ?? 'Unknown service').' | '.$date.' | '.$time."\n".$notes]);
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['page_id'] = ['required', 'integer', Rule::exists('pages', 'id')->where('template', 'salon-booking')->where('status', 'published')];
        $rules['notes'] = 'nullable|string|max:2500';
        $rules['phone'] = ['required', 'regex:/^\+?[0-9 ()-]{8,24}$/'];
        $rules['email'] = 'nullable|email:rfc|max:160';
        $rules['extra'] = 'required|array:form,service_id,date,time';
        $rules['extra.form'] = ['required', Rule::in(['booking'])];
        $rules['extra.service_id'] = ['required', 'integer', Rule::exists('salon_services', 'id')->where('active', true)];
        $rules['extra.date'] = ['required', 'date_format:Y-m-d', 'after_or_equal:'.now('America/New_York')->toDateString(), 'before_or_equal:'.now('America/New_York')->addDays(90)->toDateString()];
        $rules['extra.time'] = ['required', Rule::in(['morning', 'afternoon', 'evening', 'flexible'])];

        return $rules;
    }
}
