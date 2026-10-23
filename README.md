> **Current client:** THE K LUXURY NAIL BAR — five English pages, white/gold design, dynamic 58-service catalog and MySQL-backed contact requests. See [client setup and content map](docs/clients/the-k-luxury-nail-bar.md), [verification](docs/THE_K_VERIFICATION.md) and [asset provenance](docs/THE_K_ASSETS.md). For this client, optimize media and run `php artisan db:seed --class=NailSalonSeeder`; the generic Forma demo command below is for starter examples.
# Laravel Landing Starter

Base cho các website khách hàng độc lập (1–5 trang): Laravel 12, Filament 5, Blade, Tailwind 4, Alpine, Vite. CMS thực dùng database; không phải multi-tenant hay page builder kéo thả. Demo “Forma Studio” dùng cùng services và components với dự án thật.

Yêu cầu **PHP stable >=8.5.12 <8.6**, Composer 2, Node 22+, extensions theo `starter:check`. Dự án dùng MySQL ở local và production; SQLite in-memory chỉ dùng cho automated feature tests độc lập. Laragon trên máy này đã được cấu hình PHP 8.5.12; xem [cấu hình PHP/MySQL local](docs/LOCAL_PHP_MYSQL.md). `.tools/php` là runtime dự phòng, bị Git bỏ qua.

## Quick start

```sh
cp .env.example .env
composer install
php artisan key:generate
# Tạo database MySQL và điền DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD trong .env.
php artisan migrate
npm ci
npm run build
php artisan storage:link
php artisan starter:setup --demo
php artisan starter:setup --admin-email=you@your-company.example
php artisan serve
```

Password admin được nhập kín, tối thiểu 14 ký tự; không có tài khoản/mật khẩu mặc định. Chỉ user `is_admin=true` vào được `/admin`. Đổi mật khẩu tại Profile. Password reset chỉ bật nếu `MAIL_MAILER` khác `log`/`array`; hãy kiểm thử SMTP thật trước khi giao khách. `starter:setup` giữ nguyên tài khoản đã tồn tại, không tự nâng quyền.

Windows trong workspace đã chuẩn bị: mở terminal Laragon với PHP 8.5.12 rồi chạy `php artisan serve`, hoặc chạy `.\scripts\dev.ps1`, rồi mở `http://127.0.0.1:8000`. Script ưu tiên PHP đạt baseline trong PATH, chỉ dùng `.tools/php` dự phòng khi cần và chỉ thay PATH của phiên hiện tại. PHP 8.3 không đạt baseline của dự án.

Trong terminal PowerShell khác, chọn runtime trước khi chạy Composer/Artisan:

```powershell
$env:PATH = 'C:\laragon\bin\php\php-8.5.12-nts-Win32-vs17-x64;' + $env:PATH
$env:STARTER_PHP = (Get-Command php).Source
php -v
composer quality
```

## Lệnh thường dùng

```sh
php artisan starter:setup --name="Tên khách" --domain=https://client.example
php artisan starter:check
php artisan starter:check --production
php artisan starter:seo-audit
php artisan starter:media-audit
npm run media:optimize
php artisan starter:export-demo
composer quality
npm run test:browser
```

`npm run dev` chạy Vite riêng trong lúc `php artisan serve`. Browser tests cần `npx playwright install chromium`; dùng `STARTER_PHP` nếu PHP đúng không nằm trong PATH. Không chạy browser fixture vào database có dữ liệu thật. Showcase: `APP_ENV=local`, `STARTER_SHOWCASE=true`, truy cập `/starter/showcase`.

## Dự án khách tiếp theo

1. Dùng GitHub Template hoặc clone thành repository độc lập. Giữ lockfiles, bỏ `.env`, database/media/runtime local; tạo APP_KEY mới.
2. Điền `docs/CLIENT_BRIEF.md`; yêu cầu Codex dùng `$landing-project-bootstrap`, rồi `$landing-page-design` với brief/hình tham khảo.
3. `starter:setup --name=... --domain=... --admin-email=...` sau migrate. Không dùng `--demo` cho khách production. Trang mới ở trạng thái draft; xuất bản sau khi hoàn thiện nội dung.
4. Chỉnh brand, feature flags, menu, nội dung, SEO và ảnh trong admin. Đặt canonical origin bằng `SITE_URL`; đừng dùng domain demo.
5. Chạy quality gates, kiểm tra thực tế, export demo có chọn lọc; triển khai theo tài liệu hosting.

## Tài liệu & hiện trạng

- [Kiến trúc](docs/ARCHITECTURE.md), [catalog](docs/COMPONENTS.md), [theme](docs/DESIGN_SYSTEM.md), [AI workflow](docs/AI_WORKFLOW.md).
- [SEO](docs/SEO.md), [media](docs/MEDIA.md), [performance](docs/PERFORMANCE.md), [QA](docs/QA_CHECKLIST.md).
- [Vercel](docs/DEPLOY_VERCEL.md), [PHP hosting](docs/DEPLOY_HOSTING.md), [deploy checklist](docs/DEPLOY_CHECKLIST.md).
- [Audit môi trường](docs/ENVIRONMENT_AUDIT.md), [kết quả thực thi](docs/VERIFICATION.md).

Dynamic PHP Vercel **chưa được bật** vì chưa có bằng chứng patch runtime. Static export được kiểm tra local; triển khai trên tài khoản Vercel/hosting thật cần credentials và xác minh sau deploy. Điểm Lighthouse/Core Web Vitals là mục tiêu cần đo, không được suy ra từ build/tests.

