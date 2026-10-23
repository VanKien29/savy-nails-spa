<?php

namespace App\Filament\Resources;

use App\Models\Media;
use App\Models\Page;
use App\Services\SectionRegistry;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class PageResource extends StarterResource
{
    protected static ?string $model = Page::class;

    protected static ?string $modelLabel = 'trang';

    protected static ?string $pluralModelLabel = 'Trang nội dung';

    public static function mediaOptions(): array
    {
        return Media::where('status', 'ready')->pluck('name', 'id')->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            F\TextInput::make('title')->label('Tiêu đề')->required()->maxLength(160),
            F\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(180)->helperText('Ví dụ: dich-vu/nhan-dien. Không nhập dấu / ở đầu.'),
            F\Select::make('template')->options(['sections' => 'Sections linh hoạt', 'studio' => 'Studio — custom Blade'])->default('sections')->required(),
            F\Select::make('status')->options(['draft' => 'Bản nháp', 'published' => 'Đã xuất bản'])->default('draft')->required(),
            F\DateTimePicker::make('published_at')->label('Ngày xuất bản')->seconds(false),
            F\Toggle::make('is_home')->label('Trang chủ'), F\Toggle::make('noindex')->label('Không index'),
            F\Toggle::make('exportable')->label('Cho phép export demo')->helperText('Chỉ bật khi nội dung và ảnh được phép đưa vào bản demo.'),
            F\Select::make('hero_media_id')->label('Ảnh hero')->options(fn () => self::mediaOptions())->searchable(),
            F\Select::make('og_media_id')->label('Ảnh chia sẻ')->options(fn () => self::mediaOptions())->searchable(),
            F\TextInput::make('seo_title')->label('SEO title')->maxLength(160), F\Textarea::make('seo_description')->label('SEO description')->maxLength(320),
            F\Repeater::make('alternates')->label('Hreflang')->schema([F\TextInput::make('locale')->required(), F\TextInput::make('url')->url()->required()])->defaultItems(0),
            F\Repeater::make('sections')->label('Các section')->columnSpanFull()->reorderable()->collapsible()->itemLabel(fn (array $state): string => $state['heading'] ?? $state['type'] ?? 'Section')->schema([
                F\Select::make('type')->options(SectionRegistry::TYPES)->required(),
                F\Select::make('variant')->options(['default' => 'Mặc định', 'centered' => 'Căn giữa', 'split' => 'Chia đôi', 'background' => 'Ảnh nền'])->default('default')->required(),
                F\Toggle::make('enabled')->default(true), F\TextInput::make('id')->label('Anchor ID')->maxLength(60),
                F\TextInput::make('eyebrow')->maxLength(120), F\TextInput::make('heading')->maxLength(240), F\Textarea::make('body')->maxLength(4000),
                F\TextInput::make('button_label')->maxLength(80), F\TextInput::make('button_url')->maxLength(300),
                F\Select::make('media_id')->options(fn () => self::mediaOptions())->searchable(),
                F\Repeater::make('items')->schema([
                    F\TextInput::make('title')->maxLength(180), F\Textarea::make('text')->maxLength(2000),
                    F\TextInput::make('value')->maxLength(60), F\TextInput::make('label')->maxLength(100),
                    F\Select::make('media_id')->options(fn () => self::mediaOptions())->searchable(),
                ])->defaultItems(0)->maxItems(24),
            ])->defaultItems(0)->maxItems(30),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')->searchable(), TextColumn::make('slug')->searchable(), TextColumn::make('status')->badge(), TextColumn::make('updated_at')->dateTime()->sortable()])
            ->filters([SelectFilter::make('status')->options(['draft' => 'Bản nháp', 'published' => 'Đã xuất bản'])])
            ->recordActions([EditAction::make(), Action::make('preview')->label('Xem trước')->url(fn (Page $record) => URL::temporarySignedRoute('pages.preview', now()->addMinutes(30), ['page' => $record]))->openUrlInNewTab()]);
    }

    public static function getPages(): array
    {
        return ['index' => PageResource\Pages\ListPages::route('/'), 'create' => PageResource\Pages\CreatePage::route('/create'), 'edit' => PageResource\Pages\EditPage::route('/{record}/edit')];
    }
}
