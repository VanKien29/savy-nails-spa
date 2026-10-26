# THE K — cài đặt trên hosting PHP

Gói này đã có thư viện PHP production (vendor), CSS/JS đã build, ảnh đang quản lý trong CMS và database hiện tại. Không cần Node.js, npm hoặc cài lại Composer. Ảnh gốc phục vụ thiết kế trong resources/media, công cụ Windows và dữ liệu kiểm thử không nằm trong gói hosting. Các ảnh upload gốc mà CMS còn quản lý vẫn được giữ trong storage riêng.

## 1. Yêu cầu hosting

PHP 8.4.27 trở lên trong nhánh 8.4 (khuyến nghị cho gói này), hoặc PHP 8.5.12 trở lên trong nhánh 8.5. MySQL 8, các extension Laravel/Filament bao gồm gd, intl, pdo_mysql, exif, mbstring, openssl, fileinfo, ctype, curl, dom, xml và zip. Bật HTTPS và mod_rewrite nếu dùng Apache. Chọn PHP 8.4 trong hosting và xác nhận bản vá ít nhất 8.4.27 cho cả website lẫn lệnh CLI; PHP 8.3, 8.4.0–8.4.26 và 8.5.0–8.5.11 không đáp ứng yêu cầu.

Các mốc bản vá theo [thông báo PHP ngày 21/10/2026](https://www.php.net/archive/2026.php#2026-10-21-3). Composer khóa nền tảng ở PHP 8.4.27 để thư viện cài sau này vẫn tương thích PHP 8.4.

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
