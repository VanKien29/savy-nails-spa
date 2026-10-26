<?php

namespace App\Filament\Resources;

use App\Services\CmsAccess;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

abstract class StarterResource extends Resource
{
    public static function canViewAny(): bool
    {
        return CmsAccess::allows(CmsAccess::area(static::getModel()));
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
