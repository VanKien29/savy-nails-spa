# Savy source and data cleanup

This is the independent Savy Nails & Spa project. Local runtime uses `savy_nails_spa`; feature tests use only `savy_nails_spa_testing`. Hosting keeps `edvxadnmhosting_savynails`, its own credentials and APP_KEY outside Git.

The active site has five pages, the approved 31-service menu, Savy contacts/socials, S favicon, transparent wordmark and contact-only appointments. The old public layout and client-specific seed chain were removed. Compatibility key `salon-home` now renders Savy.

## Existing hosting update

Back up the hosting database and storage using cPanel first. In the directory containing artisan:

```sh
git pull
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan optimize:clear
```

Upload/extract the supplied asset archive into this project root. It includes `public/build/`, `public/assets/optimized/` and the Savy favicon files. Alternatively run `npm ci`, `npm run media:optimize`, `npm run build` on hosting.

```sh
php artisan savy:cleanup
php artisan savy:cleanup --apply --replace-legacy-menu
php artisan optimize:clear
php artisan optimize
php artisan starter:check --production
php artisan starter:seo-audit
```

The replacement option acts only when legacy categories and sentinel services are detected, without the approved Savy categories. It replaces that catalog with the approved Savy menu; later runs preserve Savy service IDs/prices. Without the option, cleanup refuses a detected legacy menu. This works without Tinker or shell_exec and refuses a database whose name does not contain `savy`.

Before changes it backs up content records and removed media bytes privately in `storage/app/private/backups/`. It repairs templates, stale metadata, branding and image references, then audits the content tables for leftovers. Retired page IDs can remain as drafts for lead references, with obsolete copy stripped. Admin email/password and customer leads are preserved. Old brand strings exist in code only as migration recognition and regression fixtures, never as rendering defaults.

The original local database was preserved after cloning; a full SQL backup is outside the repository. Do not delete a separate application's database.

## Local verification — 5 November 2026

PHP 8.5.12: `composer quality` passed, followed by the final expanded suite: 93 tests / 780 assertions. The six Savy migration tests include failed-backup protection and preservation of owner edits. All five affected Playwright checks passed (four in the combined run, the long admin flow in its focused rerun after increasing its overall time budget). Public checks cover five routes at three viewport sizes, menu, lightbox, keyboard controls and automatic Google Maps loading; admin checks cover current modules and menu CRUD.

`savy:cleanup --apply` passed on the separate local Savy database, retaining 31 services with no legacy content reported. CLI application/media checks passed; an oversized private original is served through responsive variants. Composer/npm dependency audits, Pint, media regression and Vite build passed. Hosting DB mutation, hosting web PHP version and remote CI remain unverified.

All five production-mode local SEO routes passed. Static export and its Chromium verifier passed for all five routes: noindex, one H1, decoded images, stylesheet/script responses, no private files and no page script errors. The verifier used a conventional static HTTP server; PHP's development-server index fallback was unsuitable for missing-path status checks. Public home and gallery-editor screenshots were inspected.

The hosting asset archive `savy-only-hosting-assets-20261105.zip` contains 201 allowlisted files (5,625,738 bytes), including Vite and optimized-image manifests. It contains no environment, SQL, PHP or retired asset paths. SHA-256: `2ae1dcdd8845cc37275f299dc0a7cfe7dee45ef05d5ad269fa78312da5603ddc`.

The lock file also includes the 4 November Symfony security patches: http-foundation/http-kernel/routing 7.4.23 and html-sanitizer/yaml 8.1.11. Run Composer install on hosting to receive those exact versions; do not run Composer update there. Sources: https://symfony.com/cve-2026-118517 and https://github.com/symfony/symfony/security/advisories/GHSA-3h27-hjv7-fqjx.

## SEO evidence

Hosting home measured on 4 November 2026: mobile and desktop each 100/100/100/100 (Performance/Accessibility/Best Practices/SEO). Mobile LCP 1.8s, TBT 0ms, CLS 0.
Report: https://pagespeed.web.dev/analysis/https-savy-nails-spa-web-landing-com/6imelg3jkz?form_factor=mobile

The score did not detect stale metadata/catalog data: the hosting services page still had 55 old services during the audit. Apply the migration and verify all five rendered pages, schema/contact data, images and sitemap/robots. Local changes do not constitute a hosting database update.
