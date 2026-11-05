# SAVY — cài đặt trên hosting PHP

Gói này đã có thư viện PHP production (vendor), CSS/JS đã build, ảnh đang quản lý trong CMS và database hiện tại. Không cần Node.js, npm hoặc cài lại Composer. Ảnh gốc phục vụ thiết kế trong resources/media, công cụ Windows và dữ liệu kiểm thử không nằm trong gói hosting. Các ảnh upload gốc mà CMS còn quản lý vẫn được giữ trong storage riêng.

## 1. Yêu cầu hosting

Ứng dụng tương thích PHP 8.4.26 trở lên trong nhánh 8.4, hoặc PHP 8.5.11 trở lên trong nhánh 8.5. MySQL 8, các extension Laravel/Filament bao gồm gd, intl, pdo_mysql, exif, mbstring, openssl, fileinfo, ctype, curl, dom, xml và zip. Bật HTTPS và mod_rewrite nếu dùng Apache. Chọn cùng nhánh PHP cho website và CLI; bật intl trong PHP Selector của nhánh đó.

Khả năng chạy ứng dụng không xác nhận bản vá bảo mật của PHP. Dùng upstream PHP 8.4.27+/8.5.12+ hoặc gói đã backport bản vá của nhà cung cấp. CloudLinux xác nhận alt-php84 8.4.26-5 và alt-php85 8.5.11-5 đã có bản vá; php -v không hiển thị số release -5 nên cần kiểm tra gói/changelog với hosting. Xem [hướng dẫn xác minh của CloudLinux](https://cloudlinux.zendesk.com/hc/en-us/articles/31194180517916-PHP-CVE-2026-109618-CVE-2026-109619-PHP2Shell-fixed-alt-php-and-ea-php-packages-and-how-to-verify). Composer khóa nền tảng ở PHP 8.4.26 để thư viện cài sau này vẫn tương thích PHP 8.4.

## 2. Upload và cấu hình web root

Giải nén toàn bộ gói vào thư mục ứng dụng. Trỏ Document Root của domain vào thư mục public của ứng dụng. Không đặt toàn bộ mã nguồn vào thư mục công khai của domain; .env, vendor và database phải ở ngoài web root.

Cấp quyền ghi cho storage và bootstrap/cache (thường 775, tùy user/group của hosting).

## 3. Database và môi trường

Tạo một database MySQL trống và tài khoản được cấp quyền. Import database/hosting.sql vào database đó bằng phpMyAdmin hoặc mysql. File SQL có dữ liệu website hiện tại và tài khoản CMS hiện tại; không có phiên đăng nhập, token đặt lại mật khẩu, cache hoặc queue cũ. Giữ file này riêng tư.

Sao chép .env.hosting.example thành .env. Điền APP_URL và SITE_URL bằng domain HTTPS thực tế, cùng DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME và DB_PASSWORD của hosting. APP_ENV phải là production, APP_DEBUG=false. APP_KEY sẽ được tạo ở bước tiếp theo.

Không chạy db:seed hay migrate:fresh sau khi import: database đã có nội dung và chỉnh sửa hiện tại.

## 4. Chạy các lệnh trong thư mục ứng dụng

```sh
php artisan key:generate --force
php artisan package:discover --ansi
php artisan storage:link
php artisan optimize
```

Nếu hosting cấm symlink, cần nhờ nhà cung cấp ánh xạ public/storage tới storage/app/public. Không đưa storage/app/private ra ngoài web.

## 5. Kiểm tra

Mở trang chủ, Services, Contact và /admin/login; kiểm tra logo, ảnh, bảng giá và đăng nhập bằng tài khoản CMS hiện tại. Nếu đổi domain sau khi optimize, chạy lại php artisan optimize:clear rồi php artisan optimize. Cấu hình SMTP riêng nếu cần gửi email; mặc định đang dùng log.

## 6. Cập nhật sau khi pull code trên CloudLinux

Đẩy bản sửa từ máy phát triển lên Git trước, sau đó pull trong đúng thư mục ứng dụng trên hosting. Giữ nguyên .env và APP_KEY đang dùng. Không tạo lại key, không import lại database và không chạy db:seed khi cập nhật.

```bash
git pull --ff-only
export PATH="/opt/alt/php84/usr/bin:$PATH"
hash -r
php -v
php --ri intl
```

Có thể dùng /opt/alt/php85/usr/bin thay cho php84 nếu chọn PHP 8.5.11+. Nếu intl chưa có, vào Select PHP Version → chọn nhánh PHP đang dùng → Extensions → bật intl → Save/Apply. Lệnh php --ri intl chỉ kiểm tra, không tự bật extension. Composer và Filament đều cần intl, nên không dùng --ignore-platform-reqs để bỏ qua lỗi.

Sau khi xác nhận intl đã bật, chạy khối lệnh bên dưới. Khối này dừng ngay nếu có lỗi, tránh chạy Artisan khi Composer chưa cài xong:

```bash
(
    set -e
    php scripts/check-php-runtime.php
    php -r 'if (!extension_loaded("intl")) { fwrite(STDERR, "Enable intl for the selected PHP version first.\n"); exit(1); }'
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
    composer check-platform-reqs --no-dev
    php artisan optimize:clear
    php artisan migrate --force
    php artisan optimize
)
```

Nếu Artisan vẫn báo yêu cầu PHP của bản cũ, composer install chưa hoàn tất nên vendor chưa được tạo lại. Không sửa tay vendor/composer/platform_check.php. Nếu git pull báo file HUONG-DAN-HOSTING.md chưa theo dõi sẽ bị ghi đè, đổi tên file này thành bản sao lưu trước khi pull, ví dụ mv -n HUONG-DAN-HOSTING.md HUONG-DAN-HOSTING.local-backup.md.

Nếu cập nhật CSS/JS, cần npm ci và npm run build; hoặc build trên máy phát triển rồi upload public/build vì thư mục này không được lưu trong Git.
