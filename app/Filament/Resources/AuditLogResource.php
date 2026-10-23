<?php

namespace App\Filament\Resources;

use App\Models\AuditLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuditLogResource extends StarterResource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $pluralModelLabel = 'Nhật ký thay đổi';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('user_id')->label('Người dùng'), TextColumn::make('model')->searchable(), TextColumn::make('record_id'), TextColumn::make('action')->badge(), TextColumn::make('changes')->formatStateUsing(fn ($record) => json_encode($record->changes, JSON_UNESCAPED_UNICODE))->wrap(), TextColumn::make('created_at')->dateTime()->sortable()])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => AuditLogResource\Pages\ListAuditLogs::route('/')];
    }
}
