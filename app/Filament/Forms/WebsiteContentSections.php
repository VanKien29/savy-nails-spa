<?php

namespace App\Filament\Forms;

use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\WebsiteContent;
use App\Filament\Pages\ServiceMenu;
use App\Models\Page;
use Filament\Forms\Components as F;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class WebsiteContentSections
{
    public static function tabs(string $area, ?Page $page): array
    {
        return match ($area) {
            'home' => self::home($page),
            'about' => self::about(),
            'services' => self::services(),
            'gallery' => self::gallery(),
            'contact' => self::contact(),
            default => [],
        };
    }

    private static function tab(string $id, string $label, string $description, array $fields): Tab
    {
        return Tab::make($label)->key($id)->extraAttributes(['data-content-section' => $id])->schema([
            Section::make($label)->description($description)->schema($fields)->columns(2),
        ]);
    }

    private static function buttons(string $group, array $keys): Fieldset
    {
        return Fieldset::make($group === 'global' ? 'Nút đặt lịch (dùng chung với Header)' : 'Nút & liên kết')->key('buttons-'.implode('-', $keys))->schema(WebsiteContent::copyFields($group, $keys))->columns(2)->columnSpanFull();
    }

    private static function links(string $id, array $links): F\Placeholder
    {
        return F\Placeholder::make('related_'.$id)->hiddenLabel()->content(view('filament.forms.content-section-links', ['links' => $links]))->columnSpanFull();
    }

    private static function blockFields(string $id, bool $eyebrow = false, bool $heading = true): array
    {
        return [
            ...($eyebrow ? [F\TextInput::make('blocks.'.$id.'.eyebrow')->label('Tiêu đề nhỏ')->maxLength(120)] : []),
            ...($heading ? [F\TextInput::make('blocks.'.$id.'.heading')->label('Tiêu đề')->maxLength(240)] : []),
            F\Textarea::make('blocks.'.$id.'.body')->label('Nội dung')->rows(4)->maxLength(4000)->columnSpanFull(),
        ];
    }

    private static function items(string $id, bool $image = false): F\Repeater
    {
        return F\Repeater::make('blocks.'.$id.'.items')->label('Các mục trong section')->schema([
            F\TextInput::make('title')->label('Tiêu đề')->maxLength(180),
            F\Textarea::make('text')->label('Mô tả')->rows(3)->maxLength(2000),
            ...($image ? [WebsiteContent::imagePicker('label', 'Ảnh dịch vụ')] : []),
        ])->columns(2)->collapsible()->itemLabel(fn (array $state) => $state['title'] ?? 'Mục mới')->defaultItems(0)->maxItems(24)->columnSpanFull()->addActionLabel('Thêm mục')->deleteAction(fn ($action) => $action->requiresConfirmation());
    }

    private static function home(?Page $page): array
    {
        $tabs = [];
        $descriptions = [
            'hero' => 'Section đầu trang: tiêu đề lớn, ảnh slideshow và hai nút hành động.',
            'intro' => 'Section giới thiệu có ảnh bên trái, câu chuyện và nút About Us bên phải.',
            'collection' => 'Section các danh mục dịch vụ, giá từ và liên kết xem bảng giá.',
            'ritual' => 'Section trải nghiệm nổi bật: từng mục gồm ảnh, tiêu đề, mô tả và nút.',
            'lookbook' => 'Section ảnh cuộn ngang, chỉ hiển thị ảnh và nút chuyển ảnh theo thiết kế Client.',
            'journey' => 'Section các bước trải nghiệm, đánh số theo thứ tự các mục.',
            'finale' => 'Section lời mời cuối trang, ngay trước Footer.',
        ];
        foreach (collect($page?->sections ?? [])->reject(fn ($block) => ($block['id'] ?? '') === 'questions')->sortBy('ordering')->values() as $index => $block) {
            $id = $block['id'];
            $fields = self::blockFields($id, in_array($id, ['collection', 'lookbook', 'journey'], true));
            if ($id === 'hero') {
                $fields[] = F\TextInput::make('blocks.hero.items.0.title')->label('Dòng tiêu đề nhấn mạnh')->maxLength(180);
                $fields[] = WebsiteContent::imagePicker('profile.hero_slides', 'Ảnh slideshow (kéo thả để sắp xếp)', true)->rules(['required', 'array', 'min:1', 'max:6']);
                $fields[] = self::buttons('global', ['booking_label', 'booking_url']);
                $fields[] = self::buttons('home', ['menu_label', 'menu_url']);
            } elseif ($id === 'intro') {
                $fields[] = WebsiteContent::imagePicker('profile.story_image', 'Ảnh câu chuyện');
                $fields[] = self::buttons('home', ['story_label', 'story_url']);
            } elseif ($id === 'collection') {
                $fields[] = self::links('categories', ['Chỉnh menu dịch vụ, danh mục & giá' => ServiceMenu::getUrl()]);
                $fields = [...$fields, ...WebsiteContent::copyFields('home', ['price_prefix']), self::buttons('home', ['full_menu_label', 'full_menu_url'])];
            } elseif ($id === 'ritual') {
                $fields[] = self::items($id, true);
                $fields[] = self::buttons('home', ['ritual_label', 'ritual_url']);
            } elseif ($id === 'lookbook') {
                $fields[] = WebsiteContent::imagePicker('profile.home_lookbook', 'Ảnh trong section này', true);
            } elseif ($id === 'journey') {
                $fields[] = self::items($id);
            } elseif ($id === 'finale') {
                $fields[] = self::buttons('home', ['contact_label', 'contact_url']);
            } else {
                $fields[] = F\TextInput::make('blocks.'.$id.'.button_label')->label('Tên nút')->maxLength(80);
                $fields[] = F\TextInput::make('blocks.'.$id.'.button_url')->label('Liên kết nút')->rules(['nullable', 'regex:~^(?:/(?!/)[^\s]*|#[a-zA-Z][\w-]*|https://[^\s]+)$~']);
                $fields[] = WebsiteContent::mediaPicker('blocks.'.$id.'.media_id', 'Ảnh');
            }
            array_unshift($fields, F\Toggle::make('blocks.'.$id.'.enabled')->label('Hiển thị section trên website')->helperText('Tắt để ẩn section; nội dung vẫn được giữ lại.')->columnSpanFull());
            $label = sprintf('%02d. %s', $index + 1, config('cms.home_sections.'.$id, $block['heading'] ?? 'Nội dung bổ sung'));
            $tab = self::tab($id, $label, $descriptions[$id] ?? 'Nội dung bổ sung trên trang chủ.', $fields);
            if (! ($block['enabled'] ?? false)) {
                $tab->badge('Ẩn')->badgeColor('gray');
            }
            $tabs[] = $tab;
        }

        return $tabs;
    }

    private static function about(): array
    {
        return [
            self::tab('story', '01. Câu chuyện salon', 'Section đầu tiên: ảnh, hai đoạn giới thiệu, câu trích dẫn và nút xem dịch vụ.', [
                ...self::blockFields('story'),
                F\Textarea::make('blocks.promise.body')->label('Đoạn giới thiệu tiếp theo')->rows(3)->maxLength(4000)->columnSpanFull(),
                WebsiteContent::imagePicker('profile.about_image', 'Ảnh câu chuyện salon'),
                ...WebsiteContent::copyFields('about', ['frame_title', 'frame_caption', 'quote', 'quote_accent']),
                self::buttons('about', ['services_label', 'services_url']),
            ]),
            self::tab('values', '02. Giá trị & cam kết', 'Section nền sáng: tiêu đề và các cam kết được đánh số.', [
                ...WebsiteContent::copyFields('about', ['values_eyebrow']),
                F\TextInput::make('blocks.promise.heading')->label('Tiêu đề')->maxLength(240),
                self::items('promise'),
            ]),
            self::tab('brand', '03. Thương hiệu & lời mời', 'Section cuối trang: thương hiệu, lời giới thiệu và liên kết Contact Us.', [
                WebsiteContent::imagePicker('profile.about_brand_image', 'Ảnh thương hiệu & lời mời'),
                ...WebsiteContent::copyFields('about', ['brand_heading', 'brand_accent']),
                ...self::blockFields('intro', heading: false),
                ...WebsiteContent::copyFields('about', ['visit_note']),
                self::links('about_contact', ['Sửa địa chỉ & số điện thoại dùng chung' => FooterSettings::getUrl()]),
                self::buttons('about', ['contact_label', 'contact_url']),
            ]),
        ];
    }

    private static function services(): array
    {
        return [self::tab('menu', '01. Danh mục & bảng giá', 'Bảng giá là nội dung chính của trang Services. Danh mục và dịch vụ dùng chung với trang Home.', [
            self::links('services', ['Chỉnh menu dịch vụ, danh mục & giá' => ServiceMenu::getUrl()]),
            WebsiteContent::imagePicker('profile.menu_brow_images', 'Ảnh minh họa Brow Services', true, 3, true),
            ...WebsiteContent::copyFields('menu'),
            ...WebsiteContent::copyFields('contact', ['menu_empty']),
        ])];
    }

    private static function gallery(): array
    {
        return [
            self::tab('gallery-grid', '01. Thư viện ảnh', 'Chọn ảnh và kéo thả để sắp xếp thứ tự hiển thị.', [
                WebsiteContent::imagePicker('gallery_images', 'Ảnh Gallery', true, 100, true)
                    ->helperText('Chọn nhiều ảnh cùng lúc. Kéo thả để đổi thứ tự hoặc nhấn × để gỡ ảnh.'),
            ]),
            self::tab('instagram', '02. Lời mời Instagram', 'Section phía dưới lưới ảnh. Hiển thị khi đã có kênh Instagram trong cài đặt Footer.', [
                ...WebsiteContent::copyFields('gallery', ['social_heading', 'social_body', 'social_label']),
                self::links('instagram', ['Sửa đường dẫn Instagram' => FooterSettings::getUrl()]),
            ]),
        ];
    }

    private static function contact(): array
    {
        return [
            self::tab('details', '01. Thông tin liên hệ', 'Cột trái ở đầu trang: địa chỉ, số điện thoại, giờ mở cửa và các kênh liên hệ. Các thông tin này được dùng chung với Footer.', [
                ...WebsiteContent::copyFields('contact', ['heading', 'heading_accent', 'visit_label', 'directions_label', 'phone_label', 'hours_label', 'social_label']),
                F\Textarea::make('settings.address')->label('Địa chỉ')->maxLength(500)->columnSpanFull(),
                F\TextInput::make('settings.hotline')->label('Số điện thoại')->maxLength(24),
                F\TextInput::make('settings.maps_url')->label('Đường dẫn Google Maps')->url()->maxLength(1000),
                F\Repeater::make('profile.hours')->label('Giờ mở cửa')->schema([F\TextInput::make('days')->label('Ngày')->required()->maxLength(80), F\TextInput::make('time')->label('Giờ')->required()->maxLength(100)])->columns(2)->maxItems(14)->defaultItems(0)->columnSpanFull(),
                F\Textarea::make('profile.hours_note')->label('Ghi chú khi chưa có lịch mở cửa')->maxLength(1000)->columnSpanFull(),
                FooterSettings::socialLinksField('settings.social_links')->columnSpanFull(),
            ]),
            self::tab('photo', '02. Ảnh salon', 'Cột ảnh bên phải thay cho biểu mẫu liên hệ theo thiết kế Client.', [
                WebsiteContent::imagePicker('profile.contact_image', 'Ảnh trang Contact Us')->required(),
            ]),
            self::tab('map', '03. Bản đồ & chỉ đường', 'Section phía dưới thông tin liên hệ: lời mời đến salon, nút chỉ đường và bản đồ nhúng.', [
                ...WebsiteContent::copyFields('contact', ['map_heading', 'map_accent', 'map_label']),
                F\Textarea::make('settings.maps_embed_url')->label('URL bản đồ nhúng')->rows(3)->maxLength(4000)->helperText('Dán URL src từ Google Maps → Share → Embed a map.')->columnSpanFull(),
                self::links('map_address', ['Địa chỉ và đường dẫn chỉ đường dùng chung với Footer' => FooterSettings::getUrl()]),
            ]),
        ];
    }
}
