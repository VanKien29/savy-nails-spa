<?php

namespace App\Jobs;

use App\Models\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyLead implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $leadId) {}

    public function handle(): void
    {
        $lead = Lead::find($this->leadId);
        if (! $lead) {
            return;
        }
        // Notifications never contain contact details: admins inspect the authenticated panel.
        if ($email = config('starter.leads.notification_email')) {
            try {
                Mail::raw('Có yêu cầu liên hệ mới #'.$lead->id.'. Đăng nhập trang quản trị để xem.', fn ($mail) => $mail->to($email)->subject('Yêu cầu liên hệ mới'));
            } catch (\Throwable $e) {
                Log::warning('Lead email failed', ['lead_id' => $lead->id, 'exception' => $e::class]);
            }
        }
        if ($url = config('starter.leads.webhook_url')) {
            if (! str_starts_with($url, 'https://')) {
                Log::warning('Webhook requires HTTPS');

                return;
            }
            try {
                Http::timeout(8)->withHeaders(['Idempotency-Key' => 'lead-'.$lead->id])->post($url, ['event' => 'lead.created', 'lead_id' => $lead->id])->throw();
            } catch (\Throwable $e) {
                Log::warning('Lead webhook failed', ['lead_id' => $lead->id, 'exception' => $e::class]);
            }
        }
    }
}
