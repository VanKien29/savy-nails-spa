<?php

namespace App\Filament\Resources;

use App\Models\Lead;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LeadResource extends StarterResource
{
    protected static ?string $model = Lead::class;

    protected static ?string $pluralModelLabel = 'Khách liên hệ';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            F\TextInput::make('full_name')->disabled(), F\TextInput::make('phone')->disabled(), F\TextInput::make('email')->disabled(),
            F\Textarea::make('message')->disabled()->columnSpanFull(), F\TextInput::make('source_page')->disabled(),
            F\KeyValue::make('utm')->disabled(), F\KeyValue::make('extra')->disabled(), F\Toggle::make('consent')->disabled(),
            F\Select::make('status')->options(Lead::STATUSES)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('full_name')->label('Họ tên')->searchable(), TextColumn::make('phone')->searchable(), TextColumn::make('email')->searchable(), TextColumn::make('status')->formatStateUsing(fn ($state) => Lead::STATUSES[$state] ?? $state)->badge(), TextColumn::make('source_page'), TextColumn::make('created_at')->dateTime()->sortable()])
            ->defaultSort('created_at', 'desc')->filters([SelectFilter::make('status')->options(Lead::STATUSES)])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => LeadResource\Pages\ListLeads::route('/'), 'edit' => LeadResource\Pages\EditLead::route('/{record}/edit')];
    }
}
