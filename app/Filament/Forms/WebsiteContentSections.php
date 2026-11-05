<?php

namespace App\Filament\Forms;

use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\ServiceMenu;
use App\Filament\Pages\WebsiteContent;
use App\Models\Page;
use Filament\Forms\Components as F;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;

class WebsiteContentSections
{
    public static function tabs(string $area, ?Page $page): array
    {
        $tabs = match ($area) {
            'home' => self::home(), 'about' => self::about(), 'services' => self::services(),
            'gallery' => self::gallery(), 'contact' => self::contact(), default => [],
        };
        $tabs[] = self::tab('seo', 'SEO trang', 'Thông tin riêng của trang khi chia sẻ hoặc xuất hiện trên công cụ tìm kiếm.', [
            F\TextInput::make('page.seo_title')->label('Tiêu đề SEO')->maxLength(160)->columnSpanFull(),
            F\Textarea::make('page.seo_description')->label('Mô tả SEO')->maxLength(320)->rows(3)->columnSpanFull(),
            WebsiteContent::mediaPicker('page.og_media_id', 'Ảnh chia sẻ của trang'),
            F\Toggle::make('page.noindex')->label('Không lập chỉ mục trang này'),
        ]);

        return $tabs;
    }

    private static function tab(string $id, string $label, string $description, array $fields, bool $toggle = false): Tab
    {
        if ($toggle) {
            array_unshift($fields, F\Toggle::make('blocks.savy-'.$id.'.enabled')->label('Hiển thị section trên website')->columnSpanFull());
        }

        return Tab::make($label)->key($id)->extraAttributes(['data-content-section' => $id])->schema([
            Section::make($label)->description($description)->schema($fields)->columns(2),
        ]);
    }

    private static function heading(string $id, string $label = 'Tiêu đề'): F\TextInput
    {
        return F\TextInput::make('blocks.savy-'.$id.'.heading')->label($label)->maxLength(240)->columnSpanFull();
    }

    private static function body(string $id, string $label = 'Nội dung'): F\Textarea
    {
        return F\Textarea::make('blocks.savy-'.$id.'.body')->label($label)->rows(4)->maxLength(4000)->columnSpanFull();
    }

    private static function image(string $key, string $label): F\ViewField
    {
        return WebsiteContent::imagePicker('profile.savy.images.'.$key, $label);
    }

    private static function buttons(string $id): array
    {
        return [
            F\TextInput::make('blocks.savy-'.$id.'.button_label')->label('Chữ trên nút / liên kết')->maxLength(80),
            F\TextInput::make('blocks.savy-'.$id.'.button_url')->label('Đường dẫn')->maxLength(500)
                ->rules(['nullable', 'regex:~^(?:/(?!/)[^\s]*|#[a-zA-Z][\w-]*|https://[^\s]+)$~']),
        ];
    }

    private static function link(string $id, string $label, string $url): F\Placeholder
    {
        return F\Placeholder::make('related_'.$id)->hiddenLabel()->content(view('filament.forms.content-section-links', ['links' => [$label => $url]]))->columnSpanFull();
    }

    private static function banner(string $area): Tab
    {
        return self::tab('banner', '01. Ảnh bìa & tiêu đề', 'Banner đầu trang đúng theo giao diện Client hiện tại.', [
            self::heading('banner'), self::image($area.'_banner', 'Ảnh nền banner'),
        ]);
    }

    private static function menu(string $label, bool $home = false): Tab
    {
        return self::tab('menu', $label, $home ? 'Trang chủ hiển thị danh mục dịch vụ đầu tiên theo thứ tự trong Menu dịch vụ.' : 'Tất cả danh mục và dịch vụ đang bật được hiển thị theo thứ tự trong Menu dịch vụ.', [
            self::heading('menu'), self::image('menu', 'Ảnh nền bảng giá (dùng chung Home và Services)'),
            self::link('services', 'Sửa danh mục, dịch vụ, giá và thứ tự', ServiceMenu::getUrl()),
            ...WebsiteContent::copyFields('menu', ['inquire_label', 'fill_label', 'gel_label', 'includes_label']),
            ...WebsiteContent::copyFields('contact', ['menu_empty']),
            ...($home ? self::buttons('menu') : []),
        ], $home);
    }

    private static function home(): array
    {
        return [
            self::tab('hero', '01. Ảnh bìa & lời chào', 'Ảnh nền toàn màn hình, logo salon, vòng trang trí và dòng địa điểm.', [
                self::heading('hero', 'Dòng địa điểm dưới logo'), self::image('hero', 'Ảnh nền đầu trang'),
                self::image('hero_logo', 'Logo lớn trên ảnh bìa'), self::image('circle', 'Vòng trang trí quanh logo'),
            ], true),
            self::tab('intro', '02. About Us', 'Khối giới thiệu nền vàng, ảnh nền và địa chỉ dùng chung với Footer.', [
                self::heading('intro'), F\TextInput::make('blocks.savy-intro.eyebrow')->label('Câu chào in đậm')->maxLength(120)->columnSpanFull(),
                self::body('intro'), F\TextInput::make('blocks.savy-intro.items.0.text')->label('Câu dẫn trước địa chỉ')->maxLength(2000)->columnSpanFull(),
                self::image('intro', 'Ảnh nền giới thiệu'), self::link('address', 'Sửa địa chỉ salon', FooterSettings::getUrl()),
            ], true),
            self::menu('03. Services', true),
            self::tab('feature', '04. Your Savy Moment', 'Ảnh bên trái, lời giới thiệu bên phải và liên kết liên hệ.', [
                self::heading('feature'), self::image('feature', 'Ảnh minh họa'), self::image('feature_background', 'Ảnh nền trang trí'),
                self::body('feature', 'Câu nổi bật (xuống dòng để tách câu)'),
                F\Textarea::make('blocks.savy-feature.items.0.text')->label('Đoạn giới thiệu')->maxLength(2000)->columnSpanFull(), ...self::buttons('feature'),
            ], true),
            self::tab('gallery', '05. Gallery', 'Ảnh cuộn ngang trên Home được chọn và sắp xếp riêng với trang Gallery.', [
                self::heading('gallery'), WebsiteContent::imagePicker('profile.savy.home_gallery', 'Ảnh trên trang chủ', true, 24, true),
                self::image('home_gallery_background', 'Ảnh nền Gallery'), ...self::buttons('gallery'),
            ], true),
            self::tab('faq', '06. FAQ', 'Sửa câu hỏi, thêm/xóa và kéo thả để đổi thứ tự. Câu trả lời có thể lấy trực tiếp thông tin dùng chung.', [
                self::heading('faq'), F\Repeater::make('blocks.savy-faq.items')->label('Câu hỏi thường gặp')->schema([
                    F\TextInput::make('title')->label('Câu hỏi')->required()->maxLength(180)->columnSpanFull(),
                    F\Select::make('value')->label('Nguồn câu trả lời')->options(['custom' => 'Nhập nội dung riêng', 'address' => 'Địa chỉ salon', 'hours' => 'Giờ mở cửa', 'booking' => 'Số điện thoại & đặt lịch'])->default('custom')->required()->live(),
                    F\Textarea::make('text')->label('Câu trả lời')->maxLength(2000)->rows(3)->visible(fn (Get $get) => $get('value') === 'custom')->columnSpanFull(),
                ])->itemLabel(fn ($state) => $state['title'] ?? 'Câu hỏi mới')->defaultItems(0)->maxItems(24)->reorderable()->collapsible()->addActionLabel('Thêm câu hỏi')->columnSpanFull(),
            ], true),
        ];
    }

    private static function about(): array
    {
        return [self::banner('about'), self::tab('intro', '02. Giới thiệu salon', 'Tiêu đề và đoạn giới thiệu ngay dưới banner.', [self::heading('intro'), self::body('intro')], true),
            self::tab('strengths', '03. Our Strengths', 'Các thẻ ảnh, tiêu đề, mô tả và nút liên hệ phía cuối trang.', [
                self::heading('strengths'), F\Repeater::make('blocks.savy-strengths.items')->label('Các điểm nổi bật')->schema([
                    WebsiteContent::imagePicker('label', 'Ảnh minh họa'), F\TextInput::make('title')->label('Tiêu đề')->maxLength(180),
                    F\Textarea::make('text')->label('Nội dung')->rows(3)->maxLength(2000)->columnSpanFull(),
                ])->itemLabel(fn ($state) => $state['title'] ?? 'Mục mới')->reorderable()->collapsible()->defaultItems(0)->maxItems(24)->addActionLabel('Thêm mục')->columnSpanFull(),
                ...self::buttons('strengths'),
            ], true)];
    }

    private static function services(): array
    {
        return [self::banner('services'), self::menu('02. Danh mục & bảng giá')];
    }

    private static function gallery(): array
    {
        return [self::banner('gallery'), self::tab('gallery', '02. Thư viện ảnh', 'Danh sách ảnh trên trang Gallery. Chọn nhiều ảnh, kéo thả hoặc dùng nút mũi tên để đổi thứ tự.', [
            WebsiteContent::imagePicker('gallery_images', 'Ảnh Gallery', true, 100, true),
        ])];
    }

    private static function contact(): array
    {
        return [self::banner('contact'), self::tab('details', '02. Thông tin liên hệ', 'Cột thông tin bên trái. Số điện thoại, địa chỉ, giờ và mạng xã hội dùng chung với Footer và FAQ.', [
            self::heading('details'),
            F\TextInput::make('blocks.savy-details.items.0.title')->label('Tiêu đề giờ mở cửa')->maxLength(180),
            F\TextInput::make('blocks.savy-details.items.1.title')->label('Tiêu đề mạng xã hội')->maxLength(180),
            F\Textarea::make('settings.address')->label('Địa chỉ')->maxLength(500)->columnSpanFull(),
            F\TextInput::make('settings.hotline')->label('Số điện thoại')->maxLength(24),
            F\TextInput::make('settings.maps_url')->label('Đường dẫn Google Maps')->url()->maxLength(1000),
            F\Repeater::make('profile.hours')->label('Giờ mở cửa')->schema([F\TextInput::make('days')->label('Ngày')->required()->maxLength(80), F\TextInput::make('time')->label('Giờ')->required()->maxLength(100)])->columns(2)->maxItems(14)->defaultItems(0)->columnSpanFull(),
            F\Textarea::make('profile.hours_note')->label('Ghi chú khi chưa có giờ mở cửa')->maxLength(1000)->columnSpanFull(),
            FooterSettings::socialLinksField('settings.social_links')->columnSpanFull(),
        ]), self::tab('card', '03. Lời mời đặt lịch', 'Khung bên phải dùng nút gọi và mạng xã hội, đúng theo Client hiện tại.', [
            self::heading('card'), self::body('card'),
            F\TextInput::make('blocks.savy-card.items.0.title')->label('Tiền tố nút gọi điện')->maxLength(180),
            F\TextInput::make('blocks.savy-card.items.1.title')->label('Tiền tố nút mạng xã hội')->maxLength(180),
            self::image('contact_background', 'Ảnh nền khu vực liên hệ'),
        ]), self::tab('map', '04. Bản đồ dùng chung', 'Bản đồ trong Footer xuất hiện trên cả năm trang.', [
            F\Textarea::make('settings.maps_embed_url')->label('URL bản đồ nhúng')->rows(3)->maxLength(4000)->columnSpanFull(),
            F\TextInput::make('profile.savy.footer.map_label')->label('Chữ liên kết mở bản đồ')->maxLength(180)->columnSpanFull(),
        ])];
    }
}
