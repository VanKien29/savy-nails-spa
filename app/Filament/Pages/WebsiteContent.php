<?php

namespace App\Filament\Pages;

use App\Filament\Forms\WebsiteContentSections;
use App\Models\Media;
use App\Models\Page as ContentPage;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Rules\ContentImage;
use App\Services\CmsAccess;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;

class WebsiteContent extends Page
{
    protected static ?string $title = 'Nội dung website';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?int $navigationSort = -90;

    protected string $view = 'filament.pages.website-content';

    #[Locked]
    public string $section = 'home';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return CmsAccess::allows('content');
    }

    public function groups(): array
    {
        return ['home' => 'Home', 'about' => 'About Us', 'services' => 'Services', 'gallery' => 'Gallery', 'contact' => 'Contact Us'];
    }

    public function mount(): void
    {
        $selected = request()->query('section', 'home');
        $this->section = is_string($selected) && array_key_exists($selected, $this->groups()) ? $selected : 'home';
        $this->fillContent();
    }

    private function page(): ?ContentPage
    {
        $template = match ($this->section) {
            'home' => 'salon-home', 'about' => 'salon-about', 'services' => 'salon-services',
            'gallery' => 'salon-gallery', 'contact' => 'salon-contact', default => null,
        };

        return $template ? ContentPage::where('template', $template)->first() : null;
    }

    public function clientUrl(): string
    {
        return $this->page()?->path() ?? '/';
    }

    private function fillContent(): void
    {
        $page = $this->page();
        $profile = app(SalonContent::class)->all()['profile'];
        $profile['about_brand_image'] ??= 'brand-logo';
        $this->form->fill(['blocks' => collect($page?->sections ?? [])->keyBy('id')->all(), 'profile' => $profile,
            'settings' => app(SiteSettingsService::class)->all(),
            'gallery_images' => SalonImage::where('show_in_gallery', true)->orderBy('sort_order')->pluck('key')->all(),
        ]);
    }

    public static function imagePicker(string $name, string $label, bool $multiple = false, int $maxImages = 24, bool $compact = false): F\ViewField
    {
        return F\ViewField::make($name)->label($label)->view('filament.forms.image-picker', ['multiple' => $multiple, 'compact' => $compact])
            ->rules($multiple ? ['nullable', 'array', 'max:'.$maxImages] : ['nullable', new ContentImage])->columnSpanFull();
    }

    public static function mediaPicker(string $name, string $label): F\ViewField
    {
        return F\ViewField::make($name)->label($label)->view('filament.forms.image-picker', ['mediaLibrary' => true, 'multiple' => false])->rules(['nullable', 'exists:media,id'])->columnSpanFull();
    }

    public static function copyFields(string $group, ?array $only = null): array
    {
        $fields = [];
        foreach (config('cms.copy.'.$group, []) as $key => [$label, $default]) {
            if ($only !== null && ! in_array($key, $only, true)) {
                continue;
            }
            $name = 'profile.copy.'.$group.'.'.$key;
            $field = strlen($default) > 95 || str_contains($key, 'body') || str_contains($key, 'note') || str_contains($key, 'consent')
                ? F\Textarea::make($name)->rows(3)->columnSpanFull() : F\TextInput::make($name);
            if ($group === 'global' && $key === 'booking_url') {
                $field->rules(['url:https'])->helperText('Mọi nút Booking trên website dùng chung đường dẫn HTTPS này.');
            } elseif (str_ends_with($key, '_url')) {
                $field->rules(['regex:~^(?:/(?!/)[^\s]*|#[a-zA-Z][\w-]*|https://[^\s]+)$~']);
            }
            $fields[] = $field->label($label)->required()->maxLength(2000);
        }

        return $fields;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Sections trên trang')
                ->key('content-sections-'.$this->section)
                ->tabs(WebsiteContentSections::tabs($this->section, $this->page()))
                ->vertical()->contained(false)->persistTabInQueryString('block')
                ->extraAttributes(['class' => 'sample-content-tabs'])->columnSpanFull(),
        ])->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $state = $this->form->getState();
        if ($this->section === 'gallery') {
            Validator::make(['data' => ['gallery_images' => $state['gallery_images'] ?? []]], [
                'data.gallery_images' => 'array|max:100',
                'data.gallery_images.*' => ['required', 'string', 'distinct', new ContentImage],
            ])->validate();
        }
        DB::transaction(function () use ($state) {
            $imageKeys = collect($state['profile'] ?? [])->only(['hero_image', 'hero_slides', 'story_image', 'about_image', 'about_brand_image', 'contact_image', 'home_lookbook', 'menu_brow_images'])->flatten()
                ->merge(collect($state['blocks'] ?? [])->pluck('items')->flatten(1)->pluck('label'))
                ->merge($state['gallery_images'] ?? [])->filter()->unique();
            foreach ($imageKeys as $key) {
                if (! preg_match('/^uploaded-media-([0-9]+)$/', (string) $key, $match) || SalonImage::where('key', $key)->exists()) {
                    continue;
                }
                $upload = Media::whereKey($match[1])->where('status', 'ready')->firstOrFail();
                SalonImage::create(['key' => $key, 'media_id' => $upload->id, 'variants' => [], 'caption' => $upload->name, 'alt' => mb_substr($upload->alt ?: $upload->name, 0, 255), 'category' => 'nails', 'sort_order' => 0]);
            }
            if ($this->section === 'gallery') {
                $items = $state['gallery_images'] ?? [];
                foreach (SalonImage::where('show_in_gallery', true)->whereNotIn('key', $items)->get() as $image) {
                    $image->update(['show_in_gallery' => false]);
                }
                foreach ($items as $order => $key) {
                    SalonImage::where('key', $key)->firstOrFail()->update(['show_in_gallery' => true, 'sort_order' => 200 + $order]);
                }
            }
            if ($page = $this->page()) {
                $page = ContentPage::whereKey($page->id)->lockForUpdate()->firstOrFail();
                $page->sections = collect($page->sections)->map(fn ($block) => array_replace_recursive($block, $state['blocks'][$block['id']] ?? []))->all();
                // Repeaters replace complete lists, including deletions.
                $page->sections = collect($page->sections)->map(function ($block) use ($state) {
                    if (array_key_exists('items', $state['blocks'][$block['id']] ?? [])) {
                        $block['items'] = $state['blocks'][$block['id']]['items'];
                    }

                    return $block;
                })->all();
                $page->save();
            }
            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            $data = $profile->data;
            foreach ($state['profile'] ?? [] as $key => $value) {
                if ($key === 'copy') {
                    foreach ($value as $group => $copy) {
                        $data['copy'][$group] = array_replace($data['copy'][$group] ?? [], $copy);
                    }
                } else {
                    $data[$key] = $value;
                }
            }
            $profile->update(['data' => $data]);
            if (! empty($state['settings'])) {
                $settings = app(SiteSettingsService::class);
                $settings->update(array_replace($settings->all(), $state['settings']));
            }
        });
        app(SalonContent::class)->forget();
        $this->fillContent();
        Notification::make()->title('Đã lưu nội dung website')->body('Nội dung đã được cập nhật trên Client.')->success()->send();
    }
}
