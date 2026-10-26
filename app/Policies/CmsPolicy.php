<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\Media;
use App\Models\Page;
use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonService;
use App\Models\User;
use App\Services\CmsAccess;
use App\Services\MediaService;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class CmsPolicy
{
    protected string $area = 'content';

    protected bool $allowsCreation = true;

    public function before(User $user, string $ability): ?bool
    {
        return ! $user->canAccessPanel(Filament::getPanel('admin')) ? false : null;
    }

    public function viewAny(User $user): bool
    {
        return CmsAccess::allows($this->area);
    }

    public function create(User $user): bool
    {
        return $this->allowsCreation && CmsAccess::allows($this->area);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return $this->area === 'content' && CmsAccess::allows('content');
    }

    public function view(User $user, Model $record): bool
    {
        return CmsAccess::allows(CmsAccess::area($record::class));
    }

    public function update(User $user, Model $record): bool
    {
        return ! $record instanceof AuditLog && $this->view($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        if (! $this->view($user, $record) || $record instanceof User || $record instanceof AuditLog) {
            return false;
        }
        if ($record instanceof Lead) {
            return CmsAccess::allows('users');
        }
        if ($record instanceof Page) {
            return ! $record->is_home;
        }
        if ($record instanceof SalonImage) {
            return ! $record->inUse();
        }
        if ($record instanceof SalonCategory) {
            return ! SalonService::where('category_id', $record->id)->exists();
        }
        if ($record instanceof Media) {
            return ! app(MediaService::class)->inUse($record);
        }

        return true;
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return false;
    }

    public function restore(User $user, Model $record): bool
    {
        return false;
    }

    public function replicate(User $user, Model $record): bool
    {
        return false;
    }
}
