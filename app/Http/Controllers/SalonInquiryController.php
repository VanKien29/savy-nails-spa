<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalonBookingRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Services\SiteSettingsService;
use Illuminate\Http\JsonResponse;

class SalonInquiryController extends Controller
{
    public function contact(StoreLeadRequest $request, LeadController $controller)
    {
        return $this->respond($request, $controller->store($request), 'Thank you. Your message has been received. Please call us if you need a prompt response.');
    }

    public function booking(SalonBookingRequest $request, LeadController $controller)
    {
        return $this->respond($request, $controller->store($request), 'Your appointment request has been received. Your visit is not confirmed until our team contacts you. For a prompt response, call '.app(SiteSettingsService::class)->get('hotline').'.');
    }

    private function respond(StoreLeadRequest $request, $response, string $message)
    {
        return $response instanceof JsonResponse ? $response->setData(['message' => $message]) : back()->with('success', $message);
    }
}
