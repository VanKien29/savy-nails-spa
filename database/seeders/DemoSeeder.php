<?php

namespace Database\Seeders;

use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo seed is only permitted in local/testing.');
        }
        if (! SiteSetting::find(1)) {
            app(SiteSettingsService::class)->update(array_replace(SiteSettingsService::defaults(), ['email' => 'hello@example.com', 'address' => 'Địa chỉ minh họa · Thành phố Hồ Chí Minh', 'legal' => 'Website mẫu. Tên, dự án và số liệu chỉ để minh họa.']));
        }
        $section = fn ($type, $heading, $body = '', $items = [], $extra = []) => array_merge(['type' => $type, 'variant' => 'default', 'enabled' => true, 'heading' => $heading, 'body' => $body, 'items' => $items], $extra);
        Page::firstOrCreate(['slug' => 'trang-chu'], [
            'title' => 'Forma — Thương hiệu bắt đầu từ khác biệt', 'template' => 'sections', 'status' => 'published', 'is_home' => true, 'exportable' => true,
            'seo_title' => 'Forma Studio — Chiến lược, thiết kế và trải nghiệm số',
            'seo_description' => 'Website minh họa của Forma Studio. Khám phá cách chiến lược thương hiệu, thiết kế và công nghệ tạo nên trải nghiệm nhất quán cho doanh nghiệp.',
            'sections' => [
                $section('hero', "Thương hiệu tốt.\nKhởi đầu khác biệt.", 'Chúng tôi kết nối chiến lược, thiết kế và công nghệ để biến những ý tưởng của bạn thành trải nghiệm thương hiệu có sức sống.', [], ['eyebrow' => 'Independent creative studio · Vietnam', 'variant' => 'split', 'button_label' => 'Bắt đầu dự án', 'button_url' => '#lien-he']),
                $section('services', "Từ ý tưởng ban đầu,\nđến dấu ấn dài lâu.", 'Một đối tác. Nhiều góc nhìn. Giải pháp được thiết kế quanh điều doanh nghiệp bạn thực sự cần.', [
                    ['title' => 'Chiến lược thương hiệu', 'text' => 'Tìm ra điều làm nên sự khác biệt. Xây nền tảng rõ ràng để thương hiệu của bạn phát triển đúng hướng.', 'label' => 'Định vị · Câu chuyện · Chiến lược'],
                    ['title' => 'Nhận diện & thiết kế', 'text' => 'Chuyển hóa cá tính thương hiệu thành một hệ thống hình ảnh nhất quán, tinh tế và dễ ghi nhớ.', 'label' => 'Nhận diện · Bao bì · Ấn phẩm'],
                    ['title' => 'Trải nghiệm số', 'text' => 'Website được thiết kế để kể câu chuyện của bạn, tạo kết nối và biến sự quan tâm thành hành động.', 'label' => 'Website · Landing page · UI/UX'],
                ], ['eyebrow' => '01 / Giải pháp', 'id' => 'dich-vu']),
                $section('gallery', 'Mỗi dự án, một góc nhìn mới.', 'Hai ý tưởng thiết kế minh họa cho cách chúng tôi tiếp cận những câu chuyện khác nhau.', [
                    ['title' => 'Lá — Sống gần thiên nhiên', 'text' => 'Ý tưởng minh họa / Chiến lược & nhận diện', 'value' => 'lá.'],
                    ['title' => 'Nâu — Hương vị nguyên bản', 'text' => 'Ý tưởng minh họa / Bao bì & trải nghiệm số', 'value' => 'nâu'],
                ], ['eyebrow' => '02 / Góc sáng tạo', 'id' => 'du-an']),
                $section('about', "Nhỏ trong quy mô.\nLớn trong sự tận tâm.", 'Chúng tôi tin những kết quả tốt bắt đầu từ việc lắng nghe đúng câu hỏi.', [
                    ['title' => 'Hiểu trước khi thiết kế', 'text' => 'Dành thời gian tìm hiểu doanh nghiệp, khách hàng và mục tiêu để mỗi quyết định đều có cơ sở.'],
                    ['title' => 'Đồng hành như một đội', 'text' => 'Làm việc trực tiếp, chia sẻ rõ ràng và cùng bạn tìm giải pháp phù hợp qua từng giai đoạn.'],
                    ['title' => 'Tạo ra giá trị có thể tiếp nối', 'text' => 'Một hệ thống nhất quán để đội ngũ của bạn có thể chủ động sử dụng, mở rộng và phát triển.'],
                ], ['eyebrow' => '03 / Về chúng tôi', 'id' => 've-chung-toi']),
                $section('statistics', 'Những con số trong bản demo.', '', [
                    ['value' => '24', 'label' => 'Dự án minh họa'], ['value' => '12', 'label' => 'Lĩnh vực minh họa'], ['value' => '06', 'label' => 'Năm kinh nghiệm (mẫu)'], ['value' => '100%', 'label' => 'Sự tập trung cho ý tưởng'],
                ], ['eyebrow' => 'Dữ liệu minh họa · Không phải thành tích thực tế']),
                $section('process', 'Rõ ràng trong từng bước.', 'Một quy trình gọn gàng giúp chúng ta tập trung vào điều quan trọng: kết quả.', [
                    ['title' => 'Lắng nghe & khám phá', 'text' => 'Thống nhất mục tiêu, chân dung khách hàng và phạm vi. Mỗi dự án bắt đầu bằng một cuộc trò chuyện cởi mở.'],
                    ['title' => 'Định hướng & sáng tạo', 'text' => 'Phát triển ý tưởng từ chiến lược, thử nghiệm hướng tiếp cận và cùng bạn tinh chỉnh giải pháp.'],
                    ['title' => 'Hoàn thiện & đồng hành', 'text' => 'Kiểm tra kỹ, bàn giao đầy đủ và hướng dẫn sử dụng để bạn tự tin đưa thương hiệu bước tiếp.'],
                ], ['eyebrow' => '04 / Cách làm việc']),
                $section('testimonials', 'Một mối quan hệ tốt,\nbắt đầu từ sự tin tưởng.', 'Các nhận xét dưới đây là nội dung giả lập để xem bố cục; không đại diện cho khách hàng thật.', [
                    ['title' => 'Khách hàng minh họa A', 'label' => 'Thương hiệu dịch vụ · Demo', 'text' => 'Chúng tôi hình dung một đối tác biết lắng nghe và giúp câu chuyện thương hiệu trở nên rõ ràng hơn.'],
                    ['title' => 'Khách hàng minh họa B', 'label' => 'Thương hiệu bán lẻ · Demo', 'text' => 'Quy trình làm việc gọn gàng, mọi quyết định thiết kế đều có một lý do dễ hiểu đằng sau.'],
                    ['title' => 'Khách hàng minh họa C', 'label' => 'Doanh nghiệp sáng tạo · Demo', 'text' => 'Một hệ thống dễ sử dụng giúp đội ngũ chủ động tiếp nối công việc sau khi dự án kết thúc.'],
                ], ['eyebrow' => '05 / Góc nhìn khách hàng']),
                $section('faq', 'Có thể bạn đang băn khoăn.', '', [
                    ['title' => 'Forma phù hợp với doanh nghiệp nào?', 'text' => 'Trang này là mẫu cho doanh nghiệp dịch vụ. Với dự án thật, chúng tôi sẽ cùng xác định nhu cầu, giai đoạn và phạm vi trước khi đề xuất.'],
                    ['title' => 'Một dự án thường kéo dài bao lâu?', 'text' => 'Thời gian phụ thuộc phạm vi, nội dung và số vòng phản hồi. Lịch trình cụ thể được thống nhất trước khi bắt đầu.'],
                    ['title' => 'Tôi có thể tự cập nhật website không?', 'text' => 'Có. Trang quản trị hỗ trợ cập nhật nội dung, ảnh, cấu hình thương hiệu, metadata SEO và quản lý liên hệ.'],
                    ['title' => 'Chi phí được xác định như thế nào?', 'text' => 'Báo giá dựa trên mục tiêu, phạm vi và độ phức tạp. Hãy chia sẻ yêu cầu để nhận đề xuất phù hợp.'],
                ], ['eyebrow' => '06 / Câu hỏi thường gặp', 'id' => 'cau-hoi']),
                $section('cta', "Ý tưởng của bạn.\nBước tiếp theo của chúng ta.", 'Dù đang bắt đầu hay sẵn sàng thay đổi, chúng tôi rất muốn nghe câu chuyện của bạn.', [], ['eyebrow' => 'Cùng tạo nên điều mới', 'button_label' => 'Chia sẻ dự án', 'button_url' => '#lien-he']),
                $section('contact', "Bắt đầu bằng\nmột lời chào.", 'Chia sẻ điều bạn đang ấp ủ. Chúng tôi sẽ cùng bạn tìm hướng đi phù hợp.', [], ['eyebrow' => '07 / Liên hệ', 'id' => 'lien-he']),
            ],
        ]);
        Page::firstOrCreate(['slug' => 'dich-vu/thiet-ke'], ['title' => 'Dịch vụ thiết kế', 'template' => 'studio', 'status' => 'published', 'exportable' => true, 'seo_title' => 'Thiết kế thương hiệu & website | Forma Studio', 'seo_description' => 'Trang dịch vụ minh họa: chiến lược nhận diện, thiết kế hệ thống hình ảnh và website phù hợp nhu cầu doanh nghiệp.', 'sections' => [
            $section('hero', 'Thiết kế để tạo kết nối.', 'Từ nhận diện đến website, mỗi điểm chạm là cơ hội kể câu chuyện thương hiệu.', [], ['variant' => 'centered', 'eyebrow' => 'Dịch vụ · Trang minh họa', 'button_label' => 'Trao đổi dự án', 'button_url' => '/#lien-he']),
            $section('benefits', 'Nền tảng cho bước phát triển mới.', '', [['title' => 'Nhất quán', 'text' => 'Hệ thống hình ảnh xuyên suốt các điểm chạm.'], ['title' => 'Dễ sử dụng', 'text' => 'Thiết kế gần gũi, phù hợp ngữ cảnh.'], ['title' => 'Có thể mở rộng', 'text' => 'Sẵn sàng cho những thay đổi tiếp theo.']]),
        ]]);
        foreach ([['Dịch vụ', '/#dich-vu'], ['Dự án', '/#du-an'], ['Về Forma', '/#ve-chung-toi'], ['Câu hỏi', '/#cau-hoi']] as $index => [$label,$url]) {
            NavigationItem::firstOrCreate(['url' => $url, 'location' => 'header'], ['label' => $label, 'sort_order' => $index, 'enabled' => true]);
        }
        NavigationItem::firstOrCreate(['url' => '/dich-vu/thiet-ke', 'location' => 'footer'], ['label' => 'Dịch vụ thiết kế', 'enabled' => true]);
        NavigationItem::firstOrCreate(['url' => '/#lien-he', 'location' => 'footer'], ['label' => 'Liên hệ', 'enabled' => true]);
    }
}
