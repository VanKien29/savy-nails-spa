<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Media;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContentObserver
{
    public function saved(Model $model): void
    {
        $this->record($model, $model->wasRecentlyCreated ? 'created' : 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted');
    }

    private function record(Model $model, string $action): void
    {
        Cache::forget('starter.navigation');
        app(SiteSettingsService::class)->forget();
        if ($model instanceof Media) {
            app(SalonContent::class)->forget();
        }
        // Audit field names; only low-risk state transitions carry values. No content, contact data, password or token.
        $changes = ['fields' => array_keys($model->getChanges())];
        foreach (['status', 'template', 'enabled', 'is_admin'] as $field) {
            if ($model->wasChanged($field)) {
                $changes[$field] = ['from' => $model->getOriginal($field), 'to' => $model->getAttribute($field)];
            }
        }
        AuditLog::create(['user_id' => auth()->id(), 'model' => $model::class, 'record_id' => $model->getKey(), 'action' => $action, 'changes' => $changes]);
    }
}
