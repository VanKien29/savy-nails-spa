<?php

namespace App\Filament\Resources;

use App\Models\Media;
use App\Services\MediaService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Schemas\Schema;
use Filament\Tables\Columns as C;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MediaResource extends StarterResource
{
    protected static ?string $model = Media::class;

    protected static ?string $pluralModelLabel = 'Thư viện ảnh';

    public static function canDelete(Model $record): bool
    {
        return parent::canDelete($record) && ! app(MediaService::class)->inUse($record);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            F\FileUpload::make('file')->label('Ảnh gốc')->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])->maxSize(8192)->storeFiles(false)->required()->visibleOn('create'),
            F\TextInput::make('name')->disabled()->visibleOn('edit'),
            F\TextInput::make('alt')->label('Mô tả thay thế')->maxLength(500),
            F\TextInput::make('width')->disabled()->visibleOn('edit'), F\TextInput::make('height')->disabled()->visibleOn('edit'), F\TextInput::make('status')->disabled()->visibleOn('edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            C\ImageColumn::make('preview')->getStateUsing(fn (Media $record) => $record->url()),
            C\TextColumn::make('name')->searchable(), C\TextColumn::make('mime'), C\TextColumn::make('width'), C\TextColumn::make('height'), C\TextColumn::make('size')->numeric(), C\TextColumn::make('status')->badge(),
        ])->recordActions([EditAction::make(), DeleteAction::make()->using(fn (Media $record) => app(MediaService::class)->delete($record))]);
    }

    public static function getPages(): array
    {
        return ['index' => MediaResource\Pages\ListMedia::route('/'), 'create' => MediaResource\Pages\CreateMedia::route('/create'), 'edit' => MediaResource\Pages\EditMedia::route('/{record}/edit')];
    }
}
