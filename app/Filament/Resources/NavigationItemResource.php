<?php

namespace App\Filament\Resources;

use App\Models\NavigationItem;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NavigationItemResource extends StarterResource
{
    protected static ?string $model = NavigationItem::class;

    protected static ?string $pluralModelLabel = 'Menu & footer';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([F\TextInput::make('label')->required()->maxLength(80), F\TextInput::make('url')->required()->maxLength(500), F\Select::make('location')->options(['header' => 'Header', 'footer' => 'Footer'])->default('header')->required(), F\TextInput::make('sort_order')->numeric()->default(0)->required(), F\Toggle::make('enabled')->default(true)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('label')->searchable(), TextColumn::make('url'), TextColumn::make('location'), TextColumn::make('sort_order')])->defaultSort('sort_order')->reorderable('sort_order')->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => NavigationItemResource\Pages\ListNavigationItems::route('/'), 'create' => NavigationItemResource\Pages\CreateNavigationItem::route('/create'), 'edit' => NavigationItemResource\Pages\EditNavigationItem::route('/{record}/edit')];
    }
}
