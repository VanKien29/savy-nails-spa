<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\SalonProfile;
use App\Models\SiteSetting;
use App\Models\User;

class CmsAccess
{
    public static function allows(string $area): bool
    {
        $user = auth()->user();

        return (bool) $user?->is_admin && (bool) $user->is_active;
    }

    public static function area(string $model): string
    {
        return match ($model) {
            User::class => 'users', Lead::class => 'leads', AuditLog::class => 'audit',
            SiteSetting::class => 'settings', SalonProfile::class => 'profile',
            default => 'content',
        };
    }
}
