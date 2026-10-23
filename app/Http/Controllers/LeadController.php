<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Jobs\NotifyLead;
use App\Models\Lead;
use App\Models\Page;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request)
    {
        $data = $request->validated();
        $page = Page::published()->findOrFail($data['page_id']);
        if ($secret = config('starter.leads.captcha_secret')) {
            try {
                $verified = Http::asForm()->timeout(8)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', ['secret' => $secret, 'response' => $request->input('cf-turnstile-response'), 'remoteip' => $request->ip()])->json('success');
            } catch (\Throwable) {
                $verified = false;
            }
            if (! $verified) {
                throw ValidationException::withMessages(['captcha' => __('starter.captcha_failed')]);
            }
        }
        $phone = preg_replace('/[^0-9+]/', '', $data['phone'] ?? '');
        $email = mb_strtolower(trim($data['email'] ?? ''));
        $fingerprint = hash_hmac('sha256', implode('|', [$phone, $email, trim($data['message'] ?? ''), $page->id, now()->toDateString()]), config('app.key'));
        $lead = Lead::firstOrCreate(['fingerprint' => $fingerprint], [
            'full_name' => $data['full_name'], 'phone' => $phone ?: null, 'email' => $email ?: null,
            'message' => $data['message'] ?? null, 'source_page' => $page->path(), 'utm' => $data['utm'] ?? [],
            'extra' => $data['extra'] ?? [], 'consent' => $request->boolean('consent'), 'status' => 'new',
        ]);
        if ($lead->wasRecentlyCreated) {
            try {
                NotifyLead::dispatch($lead->id)->afterCommit();
            } catch (\Throwable $exception) {
                Log::warning('Lead notification dispatch failed', ['lead_id' => $lead->id, 'exception' => $exception::class]);
            }
        }
        $message = 'Cảm ơn bạn. Yêu cầu của bạn đã được ghi nhận.';

        return $request->expectsJson() ? response()->json(['message' => $message], 201) : back()->with('success', $message);
    }
}
