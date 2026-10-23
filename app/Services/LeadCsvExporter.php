<?php

namespace App\Services;

use App\Models\Lead;

class LeadCsvExporter
{
    public function download(?string $status = null)
    {
        abort_unless(auth()->user()?->is_admin, 403);

        return response()->streamDownload(function () use ($status) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['ID', 'Họ tên', 'Điện thoại', 'Email', 'Nội dung', 'Trang', 'Trạng thái', 'Ngày'], ',', '"', '');
            foreach (Lead::query()->when($status, fn ($q) => $q->where('status', $status))->orderBy('id')->cursor() as $lead) {
                $row = [$lead->id, $lead->full_name, $lead->phone, $lead->email, $lead->message, $lead->source_page, $lead->status, $lead->created_at->toIso8601String()];
                $row = array_map(fn ($value) => self::safeCell((string) $value), $row);
                fputcsv($stream, $row, ',', '"', '');
            }
            fclose($stream);
        }, 'leads-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    public static function safeCell(string $value): string
    {
        return preg_match('/^[\s]*[=+@-]/u', $value) ? "'".$value : $value;
    }
}
