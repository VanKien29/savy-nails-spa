<?php

namespace App\Filament\Resources;

use App\Models\Redirect;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedirectResource extends StarterResource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $pluralModelLabel = 'Redirect 301';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('source')->required()->unique(ignoreRecord: true)->helperText('/duong-dan-cu'), TextInput::make('target')->required()->helperText('/duong-dan-moi. Chỉ đường dẫn nội bộ.')]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('source')->searchable(), TextColumn::make('target'), TextColumn::make('updated_at')->dateTime()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => RedirectResource\Pages\ListRedirects::route('/'), 'create' => RedirectResource\Pages\CreateRedirect::route('/create'), 'edit' => RedirectResource\Pages\EditRedirect::route('/{record}/edit')];
    }
}
