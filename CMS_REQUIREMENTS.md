# CMS — THE K LUXURY NAIL BAR

CMS được xây trên Laravel 12, Filament 5, Livewire và MySQL. Giao diện và cách tổ chức Admin được đối chiếu với source `Weblanding/1millionbeautygorey`: sidebar tối, header trắng, dashboard giới thiệu, chỉnh nội dung theo từng khu vực và thư viện ảnh dạng lưới. Logo và màu nhấn dùng thương hiệu THE K. Đường dẫn quản trị: `/admin`.

Các màn hình chính: `/admin/website-content`, `/admin/header-menu`, `/admin/footer-settings`, `/admin/media-library`, `/admin/seo-settings`, `/admin/script-widgets`. Nội dung website chọn trang ở thanh trên, chọn section được đánh số ở cột trái và chỉnh chữ, ảnh, nút cùng danh sách con trong một khung bên phải. Các section đi theo thứ tự hiển thị trên Client; Home lấy thứ tự thực từ nội dung đã lưu, gồm cả section đang ẩn để bật lại khi cần. Chuyển section giữ nguyên nội dung đang sửa; nút **Lưu nội dung** lưu thay đổi của trang đang chọn. Header quản lý menu kéo thả, logo desktop/mobile, favicon và nút đặt lịch. Các danh sách ảnh hỗ trợ kéo thả và nút đổi thứ tự.

## Chức năng và tiêu chí nghiệm thu

| Phần quản lý | Chức năng |
| --- | --- |
| Tổng quan | Số trang đang hiển thị, dịch vụ, ảnh đã tải lên, tài khoản Admin và liên kết mở Client |
| Nội dung website | Chỉnh các trang Client hiện có theo từng khu vực; không có màn Trang & xuất bản riêng |
| Khối nội dung | Tiêu đề, mô tả, nút và liên kết, ảnh, các mục con; bật/tắt, sắp xếp khối trang chủ và trang linh hoạt. Home không có section FAQ |
| Lịch sử nội dung | Database giữ tối đa 30 phiên bản trước của từng trang để hỗ trợ khôi phục khi cần |
| Dịch vụ | Thêm/sửa/xóa, danh mục, nhóm, giá thường/gel, giá từ, ghi chú, thời lượng, quyền lợi đi kèm, thứ tự, bật/tắt |
| Danh mục | Thêm/sửa/xóa, mô tả, đường dẫn và sắp xếp; chặn xóa danh mục còn dịch vụ |
| Ảnh trong nội dung | Quản lý trực tiếp ở Nội dung website → Gallery bằng lưới ảnh gọn: chọn nhiều ảnh, sắp xếp và gỡ ảnh. Không có form chú thích/mô tả, nút phóng to, ghi chú, bộ lọc hay liên kết nguồn ảnh |
| Thông tin salon | Ảnh/slideshow tại Nội dung website, giờ mở cửa tại Footer, địa chỉ có cấu trúc tại SEO; không có màn Hồ sơ salon riêng |
| Câu chữ Client | Nút và liên kết, giới thiệu, Gallery, bảng giá, thông tin liên hệ, chính sách, chân trang và thương hiệu dự phòng |
| Nhận diện | Logo desktop/mobile, favicon, màu nền/màu nhấn/màu chữ, font tiêu đề/nội dung và màu trang chủ |
| Menu | Thêm/sửa/xóa, header/footer, sắp xếp và bật/tắt |
| Hệ thống | Tài khoản Admin toàn quyền, vô hiệu hóa đăng nhập, đổi mật khẩu, nhật ký, SEO mặc định và bản đồ |

Nội dung đang hiển thị phải lấy từ database hoặc các giá trị mặc định đã có trường chỉnh sửa trong CMS. Sau khi lưu, Client nhận nội dung mới khi tải lại trang; không cần build lại để thay câu chữ, giá hoặc ảnh tải lên. Thay đổi mã nguồn/bố cục và CSS vẫn đi qua quy trình phát triển và build.

Không hiển thị hai cột trạng thái Gallery/Minh họa hoặc công tắc phân loại ảnh minh họa trong Admin. Client không hiển thị nhãn phân loại “Style inspiration”/“Treatment collection”; danh sách ảnh được quản lý trực tiếp trong nội dung Gallery.

Gallery hiển thị ảnh tĩnh trong lưới masonry, không có chú thích bên dưới, nút/hiệu ứng phóng to hoặc ghi chú dưới lưới. Bấm ảnh không mở lightbox. Admin dùng một bộ chọn nhiều ảnh, kéo thả hoặc dùng nút đổi thứ tự và gỡ ảnh trên từng thumbnail; không tạo dòng ảnh trống. Caption, alt, nhóm và nguồn ảnh cũ được giữ trong database khi sắp xếp/gỡ ảnh. Ảnh tải lên mới tự lấy tên và alt từ thư viện Media. Tối đa 100 ảnh; có thể gỡ hết ảnh mà không xóa tệp gốc.

Theo yêu cầu khách tháng 10/2026: Home có 7 section, bỏ FAQ, chữ dưới ảnh lookbook và nút Explore a Different Edit. Các trang không hiển thị mũi tên trang trí hoặc nút về đầu trang; lookbook giữ nút chuyển ảnh chevron. Footer bỏ tiêu đề Explore/Visit Us và dùng Business Hours. Contact Us dùng ảnh thay biểu mẫu; đổi ảnh tại Nội dung website → Contact Us → Ảnh salon. Booking trên Header, mobile và hero dùng chung URL HTTPS của POS, mặc định `https://lamournails6484.simplepos.us/new-home`; sửa một nơi tại Header kéo thả để áp dụng toàn website. Không có trang Booking nội bộ.

Danh mục trẻ em hiển thị Kid’s Services và ghi chú “(For children who are under 12 years old)” dưới tiêu đề; không lặp lại ghi chú tuổi dưới từng dịch vụ. Sửa ghi chú tại Nội dung website → Services.

Menu theo tài liệu khách “giá menu services.pdf” có 55 dịch vụ, theo thứ tự Brow Services → Permanent Makeup → Lash Services → Nail Services → Pedicures → Kid’s Services → Waxing. Nail Enhancements hiển thị hai cột Full Set / Fill-in; ô Fill-in để trống nếu khách không cung cấp giá. Pedicure có giá Gel Polish, mô tả và danh sách các bước. Mint Refresh thay cho Fresh Orange; các dòng Fill-in cũ được ẩn và giữ ID lịch sử. Quản lý giá Fill-in và tiêu đề/danh sách bước chăm sóc ở Dịch vụ & bảng giá; ba ảnh chân mày thay đổi tại Nội dung website → Services. Dữ liệu chuẩn nằm trong `database/data/client-menu.json`. Lần nâng cấp menu đầu tiên áp dụng danh sách khách gửi; các lần seed tiếp theo giữ chỉnh sửa từ Admin.

Danh sách dịch vụ trong Admin mặc định lọc các dịch vụ đang hiển thị. Đổi bộ lọc Hiển thị sang Không để xem các bản ghi đã ẩn.

Bảng giá dùng danh sách dịch vụ theo danh mục, gồm tên, giá thường/gel, mô tả, quyền lợi, thời lượng và ghi chú. Admin không có ô ảnh dịch vụ, ảnh danh mục, cờ nổi bật hay số phút massage riêng vì giao diện Client hiện tại không dùng các trường này. Sửa bản ghi vẫn giữ nguyên dữ liệu cũ của các trường đã bỏ khỏi form.

Admin chỉ đăng ký các màn đang dùng. Đã xóa màn Hồ sơ salon, Media dạng bảng, Menu dạng bảng, Redirect và Khách liên hệ cùng các widget/CSV đi kèm. URL trực tiếp của các màn này trả 404. Database và bản ghi liên hệ cũ được giữ nguyên. Mã dịch vụ tự sinh khi tạo và không đổi khi sửa tên; Admin không có ô mã đường dẫn cho dịch vụ.

Cấu hình website chỉ còn tên website, màu, font và hiệu ứng chuyển động. Các trường slogan, email liên hệ, legal, liên hệ nổi, vị trí trái/phải, nút về đầu trang, công tắc form và đa ngôn ngữ không xuất hiện trong Admin. Logo/favicon/Booking quản lý tại Header; doanh nghiệp, bản quyền, giờ mở cửa, liên hệ, mạng xã hội và chính sách quản lý tại Footer; SEO và địa chỉ có cấu trúc quản lý tại SEO. Đã bỏ toàn bộ mục Đo lường truy cập, các trường GA4/GTM/Meta Pixel và các trường cookie khỏi Admin. Lưu một màn không thay đổi dữ liệu của màn khác.

## Tài khoản Admin

Mọi tài khoản Admin đang hoạt động có toàn quyền quản lý nội dung, dịch vụ, ảnh, cấu hình, tài khoản và xem nhật ký. Không phân chia vai trò; các tài khoản Admin cũ thuộc nhóm Biên tập nội dung hoặc Chăm sóc khách hàng cũng có đầy đủ quyền.

Quyền truy cập được kiểm tra ở phía máy chủ, cả tài nguyên Filament, Laravel Policies và các thao tác tùy chỉnh. Tài khoản không có quyền CMS hoặc bị vô hiệu hóa không được truy cập panel. Có thể vô hiệu hóa hoặc xóa tài khoản Admin khác; xóa có hộp xác nhận, thu hồi phiên đăng nhập và token đặt lại mật khẩu. Không được xóa tài khoản đang đăng nhập hoặc xóa/vô hiệu hóa quản trị viên hoạt động cuối cùng. Nội dung, lịch sử trang và nhật ký cũ vẫn được giữ khi xóa tài khoản.

Mục Cấu hình website được ẩn khỏi menu Admin; thiết lập đang áp dụng và URL trực tiếp vẫn được giữ. Mục Hồ sơ trong menu tài khoản được ẩn, mục Đăng xuất vẫn sử dụng bình thường.

## Bảo mật đã áp dụng

- Mật khẩu được hash; chính sách mật khẩu mới tối thiểu 14 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt. Không có tài khoản/mật khẩu cố định trong source.
- Đăng nhập bằng email và mật khẩu, có giới hạn thử sai theo cơ chế Filament.
- Thay mật khẩu hoặc trạng thái hoạt động sẽ thu hồi các phiên đăng nhập database của tài khoản.
- CSRF, session encryption, cookie HttpOnly/SameSite và kiểm tra quyền phía máy chủ. Admin/Livewire trả `Cache-Control: private, no-store` và không được index.
- Nội dung thông thường được escape; liên kết, màu sắc, đường dẫn, SEO và dữ liệu lồng nhau được kiểm tra. Riêng **Script / Widget** dành cho quản trị viên có hai ô mã tự do: **Head** trước `</head>` và **Body** trước `</body>`. HTML/CSS/JavaScript/iframe được giữ nguyên và chạy khi trang Client tải, độc lập với tùy chọn cookie của các trường tracking ID. CSP trên Client cho phép tài nguyên ngoài khi có mã nhúng; CSP của Admin vẫn giữ giới hạn. Mã nhúng không được chạy trong Admin, bản xem trước có chữ ký hoặc bản export/demo.
- Upload chỉ nhận ảnh raster hợp lệ JPG/PNG/WebP/AVIF, tối đa 8 MB và 25 megapixel. Ảnh được giải mã và tạo lại các kích thước phục vụ Client; ảnh gốc nằm ở storage riêng. Không nhận upload SVG, GIF hay tệp thực thi.
- Chặn xóa ảnh còn dùng ở nội dung hiện tại hoặc lịch sử trang. Chặn xóa danh mục còn dịch vụ. Các thao tác xóa có hộp xác nhận; trang chủ không có quyền xóa trong Admin.
- Nhật ký ghi người thao tác, bản ghi, hành động và tên trường thay đổi. Không ghi giá trị mật khẩu hay dữ liệu liên hệ cá nhân. Nhật ký chỉ đọc trong CMS.
- Các API liên hệ cũ vẫn có rate limit, honeypot, token thời gian, validation và hỗ trợ Turnstile; giao diện Client không có form và Admin không có module nhận yêu cầu.

## Hướng dẫn dùng

1. Sửa chữ, ảnh và nội dung tại **Nội dung website**: chọn trang ở thanh trên, chọn section được đánh số ở cột trái rồi chỉnh nội dung bên phải. Chữ, ảnh, nút và danh sách con nằm trong đúng section sử dụng chúng. Home có công tắc **Hiển thị section trên website**; tắt chỉ ẩn section, giữ lại nội dung. Nhấn **Chọn ảnh từ Thư viện Media** để chọn ảnh có xem trước; nhấn **Lưu nội dung** để cập nhật Client. **Xem trang** mở đúng trang đang chỉnh. Contact Us cho sửa ảnh, địa chỉ, hotline, giờ mở cửa, mạng xã hội và bản đồ; các thông tin dùng chung cũng cập nhật ở Footer.
2. Sửa nút, chính sách và thông báo tại các khu vực tương ứng trong **Nội dung website**. Sửa chân trang, giờ mở cửa, liên hệ, mạng xã hội và liên kết footer tại **Cài đặt Footer**.

   Trong **Mạng xã hội → Các kênh liên hệ**, nhấn **Chọn icon** để mở thư viện hình icon có ô tìm kiếm. Có 20 kênh: Google Maps, Facebook, Instagram, TikTok, YouTube, Zalo, WhatsApp, Messenger, Telegram, X, Threads, Pinterest, LinkedIn, Discord, Snapchat, Reddit, WeChat, LINE, Viber và Twitch. Chọn icon rồi dán đường dẫn HTTPS. Kéo thả các mục để sắp xếp icon; nhấn **Lưu thay đổi** để cập nhật chân trang.
3. Chọn hoặc thay ảnh ngay tại **Nội dung website**. Mục **Gallery** quản lý danh sách ảnh và thứ tự; các khu vực khác quản lý ảnh của chính khu vực đó. Tải tệp mới tại **Thư viện ảnh**, rồi chọn nhiều ảnh cùng lúc trong trình sửa nội dung. Các nhãn trang dùng tiếng Anh như Client: **Home, About Us, Services, Gallery, Contact Us**. Đường dẫn cũ `/admin/salon-images` chuyển về khu vực Gallery.
4. Chỉnh các trang Client tại **Nội dung website** và điều hướng tại **Header kéo thả**. Đường dẫn cũ `/admin/pages` và các trang con chuyển về **Nội dung website**.
5. Cấu hình tên website, màu, font và hiệu ứng vẫn dùng được qua URL `/admin/site-settings` nhưng đã ẩn khỏi menu. Đổi logo/favicon ở **Header kéo thả**, đổi thông tin liên hệ ở **Cài đặt Footer**, đổi địa chỉ SEO ở **SEO**.
6. Dùng liên kết đổi mật khẩu ở Dashboard; mục Hồ sơ đã ẩn khỏi menu tài khoản.

## Vận hành

Windows local đã có runtime tương thích trong `.tools/php`. Khởi chạy bằng `powershell -ExecutionPolicy Bypass -File scripts/dev.ps1` khi MySQL đã chạy.

```powershell
.\.tools\php\php.exe artisan migrate
npm.cmd ci
npm.cmd run build
.\.tools\php\php.exe artisan storage:link

# Áp dụng thiết kế mới cho database đã có nội dung (sau media:optimize):
npm.cmd run media:optimize
.\.tools\php\php.exe artisan db:seed --class=SalonClientDesignSeeder

# Cập nhật menu theo PDF khách gửi (giữ các dữ liệu website khác):
.\.tools\php\php.exe artisan migrate
.\.tools\php\php.exe artisan db:seed --class=SalonClientMenuSeeder

# Tạo quản trị viên mới, mật khẩu được nhập kín:
.\.tools\php\php.exe artisan cms:create-admin --email=admin@example.com

# Tạo thông tin ngẫu nhiên; mật khẩu chỉ xuất ra terminal:
.\.tools\php\php.exe artisan cms:create-admin --generate

# Khôi phục mật khẩu bằng quyền máy chủ:
.\.tools\php\php.exe artisan cms:reset-password admin@example.com

# Kiểm tra trên database test riêng:
.\.tools\php\php.exe scripts/create-test-database.php
.\.tools\php\php.exe artisan test --compact
npm.cmd run test:media
```

Production cần HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, APP_URL/SITE_URL đúng domain và MySQL bằng tài khoản riêng theo quyền cần thiết. Chỉ trỏ document root vào `public`. Cấu hình SMTP thực trước khi bật luồng quên mật khẩu qua email; với mailer `log`/`array`, luồng này được ẩn.

Kiểm thử dùng database `the_k_luxury_nail_bar_testing` và các thư mục upload riêng trong `storage/framework/testing/disks`. Không dùng chung `storage/app/private` hoặc `storage/app/public` với website đang chạy, kể cả khi ID media trong hai database trùng nhau.

Sao lưu database, `storage/app`, ảnh optimized và APP_KEY ra nơi riêng có kiểm soát truy cập; APP_KEY cần thiết để giải mã session. Thiết lập lịch backup, kiểm tra khôi phục và lịch chạy lệnh dọn yêu cầu liên hệ theo thời gian lưu trữ trước khi vận hành production. Phiên làm việc này triển khai và kiểm tra trên local; chưa cấu hình hosting, SMTP hay backup tự động ngoài máy.
