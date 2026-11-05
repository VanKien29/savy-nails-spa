# CMS — Savy Nails & Spa

Admin: `/admin`. Giữ cách tổ chức hiện tại: chọn trang, chọn section ở cột trái, chỉnh form bên phải và nhấn **Lưu nội dung**. **Xem trang** mở đúng trang đang chỉnh.

## Nội dung website

| Trang | Các section theo thứ tự Client |
| --- | --- |
| Home | Ảnh bìa & lời chào; About Us; Services; Your Savy Moment; Gallery; FAQ; SEO trang |
| About Us | Ảnh bìa & tiêu đề; Giới thiệu salon; Our Strengths; SEO trang |
| Services | Ảnh bìa & tiêu đề; Danh mục & bảng giá; SEO trang |
| Gallery | Ảnh bìa & tiêu đề; Thư viện ảnh; SEO trang |
| Contact Us | Ảnh bìa & tiêu đề; Thông tin liên hệ; Lời mời đặt lịch; Bản đồ dùng chung; SEO trang |

Các trường chữ, nút, ảnh chính, logo hero, vòng trang trí, ảnh nền và banner lấy từ CMS. Home có công tắc bật/tắt từng section. About Us cho thêm/xóa/sắp xếp các thẻ Our Strengths. FAQ cho thêm/xóa/sắp xếp câu hỏi; câu trả lời có thể nhập riêng hoặc tự lấy địa chỉ, giờ mở cửa, số điện thoại và kênh liên hệ dùng chung.

Home dùng danh sách ảnh cuộn ngang riêng. Trang Gallery dùng danh sách riêng tối đa 100 ảnh. Chọn nhiều ảnh, kéo thả hoặc dùng nút đổi thứ tự; có thể gỡ hết ảnh. Bấm ảnh trên Client vẫn mở lightbox theo thiết kế Savy. Xóa mục khỏi danh sách không xóa tệp gốc và không phục hồi lại mục đã xóa từ dữ liệu mặc định.

Tải ảnh mới tại **Thư viện ảnh**, sau đó chọn trong form tương ứng. Ảnh đang dùng trên Client được đăng ký trong bộ chọn ảnh, gồm cả ảnh nền. Các ô xem trước có nền caro để thấy logo trắng trong suốt. Logo hero chỉnh riêng ở Home; logo Header và Footer dùng chung tại Header.

## Nội dung dùng chung

- **Header kéo thả:** logo desktop/mobile, favicon, menu/thứ tự/bật tắt và nút liên hệ. Nút chấp nhận trang nội bộ như `/contact-us` hoặc URL HTTPS.
- **Cài đặt Footer:** tiêu đề liên hệ/giờ mở cửa/nút Maps, ảnh nền, tên doanh nghiệp, bản quyền, số điện thoại, địa chỉ, URL Maps, bản đồ nhúng, giờ và mạng xã hội. Các thông tin liên hệ dùng chung với Contact Us và các FAQ tự động.
- **Menu dịch vụ:** tên, danh mục, nhóm, giá, dấu +, giá Fill-in/Gel, mô tả, ghi chú, các bước, thời lượng, thứ tự và hiển thị. Home hiển thị danh mục đầu tiên; Services hiển thị tất cả danh mục. Các trường phụ chỉ xuất hiện trên Client khi có giá trị.
- **SEO:** thiết lập mặc định và địa chỉ doanh nghiệp có cấu trúc. SEO từng trang và ảnh chia sẻ riêng chỉnh tại Nội dung website → SEO trang. Giữ nguyên hạn chế lập chỉ mục của môi trường local/preview.
- **Script / Widget, Tài khoản Admin, Nhật ký:** giữ chức năng và quyền hiện có. Admin có favicon Savy, hoặc favicon tùy chỉnh đã chọn.
- **Cấu hình website** vẫn ẩn khỏi menu: tên website và bật/tắt hiệu ứng. Không hiển thị các ô màu/font của mẫu THE K không còn được giao diện Savy sử dụng.

Bỏ các trường cũ không xuất hiện trên Client hiện tại: slideshow THE K, lời mời Instagram dưới Gallery, ảnh thay form Contact, chính sách/menu Footer và mô tả danh mục không được hiển thị. Dữ liệu cũ trong database vẫn được giữ khi các form không quản lý chúng.

## Đồng bộ cài đặt hiện có

Với website Savy đã có dữ liệu, chạy một lần sau khi tối ưu media:

```powershell
npm.cmd run media:optimize
.\.tools\php84\php.exe artisan db:seed --class=SavyCmsSeeder --no-interaction
npm.cmd run build
```

`SavyCmsSeeder` chỉ đăng ký ảnh hiện tại và bổ sung các giá trị Savy còn thiếu. Chạy lại giữ nguyên chữ, ảnh, danh sách đã chỉnh và menu dịch vụ. `SavyNailsSeeder` dành cho cài nội dung ban đầu và giữ các dịch vụ đã có.

Khi nâng cấp từ mẫu cũ, xem [hướng dẫn dọn dữ liệu Savy](SAVY_CLEANUP.md) và chạy `savy:cleanup` sau khi tải media mới. Lệnh tạo bản sao lưu riêng và giữ thông tin đăng nhập Admin; chỉ dùng `--replace-legacy-menu` khi cần thay danh mục cũ đã được phát hiện. Danh mục Savy mới gồm 31 dịch vụ trong bốn nhóm; giữ ID, giá, mô tả, trạng thái và thứ tự đã biên tập. Website nhận đặt lịch qua điện thoại/mạng xã hội.

Giữ phân quyền Laravel/Filament, media, SEO, cache và xuất preview. SEO có metadata, canonical, robots, sitemap, Open Graph và schema; production cho lập chỉ mục, bản xuất Vercel dùng noindex. Google Maps tải tự động khi gần vùng hiển thị; hero tải sớm, ảnh khác tải lazy và hiệu ứng tôn trọng thiết lập giảm chuyển động.

Giá trị khởi tạo theo Client nằm ở `config/savy.php`; chỉnh sửa đã lưu có ưu tiên. Nội dung trang dùng các section `savy-*` để tách khỏi dữ liệu mẫu cũ. Lần lưu trang đầu tiên giữ bản trước trong lịch sử trang. Lưu một trang chỉ cập nhật các trường thuộc form đó; các danh sách được thay thế toàn bộ để hỗ trợ xóa và đổi thứ tự. Không cần build lại khi đổi ảnh tải lên, chữ hoặc giá. Cache nội dung được xóa khi lưu, bao gồm cập nhật alt ảnh.

## Kiểm thử

Database local: `savy_nails_spa`. Database test riêng: `savy_nails_spa_testing`. PHPUnit kiểm tra tên database trước khi chạy migration; ảnh upload test dùng ổ đĩa fake riêng.

```powershell
.\.tools\php84\php.exe scripts/create-test-database.php
.\.tools\php84\php.exe artisan test --compact --filter='SavyCmsTest|SavyMapEmbedTest'
```

Kiểm thử CMS bao phủ lưu chữ/ảnh tải lên/banner/nút/FAQ, ẩn section, xóa danh sách, Header/Footer, thông tin dùng chung, cập nhật giá và hiển thị dịch vụ, SEO từng trang, giữ dữ liệu khu vực khác và từ chối ảnh/URL không hợp lệ. Kiểm tra trình duyệt dùng ba kích thước 1280/768/390 và thao tác lưu chữ/ảnh từ Admin đến Client.
