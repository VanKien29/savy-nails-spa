# Savy Nails & Spa — Vercel deployment

- Website: https://savy-nails-spa.vercel.app
- Project/account: vankien29/savy-nails-spa
- Deployed: 4 November 2026, Asia/Saigon.
- Source revision: aaded60 (automatic viewport-based Maps, rendering, contrast and hosting SEO preparation).
- Deployment: dpl_4L8AFAudPy1FRk9nQbxVWQ72t7hX, status READY, target production.
- Immutable URL: https://savy-nails-1zvxzhg18-vankien29.vercel.app
- Git repository: https://github.com/VanKien29/savy-nails-spa

Mode: static client preview, exported with PHP 8.5.12 from the local public Savy database. All five public pages, responsive media/fonts, gallery, FAQ, contact links and the Google Maps embed are included. Laravel/admin/database and online form persistence are not hosted on Vercel. Preview remains noindex by design; canonical points to the website URL above.

The client confirmed keeping this Vercel URL as a preview until a later PHP hosting launch. Hosting instructions: SAVY_HOSTING_SEO.md. Final external PageSpeed home-page results with automatic Maps: mobile 98/100/100/69, desktop 100/100/100/69 (Performance/Accessibility/Best Practices/SEO). Report: https://pagespeed.web.dev/analysis/https-savy-nails-spa-vercel-app/zzwbxzc1uo?form_factor=mobile (4 November, 18:27 GMT+7). The noindex preview SEO limitation is intentional; the SEO >90 target still requires the public hosting launch. Details and local production-policy checks: SEO_OPTIMIZATION_2026-11-04.md. Google Maps loads automatically within 300px of the viewport, with no activation button, a noscript iframe and the outbound Maps link.

Only the rendered artifact and static Vercel config were deployed from tmp/savy-vercel. Laravel source, environment files, customer records and public.zip were excluded. The local project association now points to Savy rather than the former THE K Vercel project. Git-based automatic builds are not configured: new website content must be exported before publishing.

Verification against the deployed public URL:

- Vercel READY; HTTPS root and all four nested pages respond successfully.
- Remote Savy Playwright suite: 3 tests passed, covering desktop/tablet/mobile routes, image loading, no overflow, menu/prices, contact-only booking, lightbox, keyboard controls and the real Google map.
- Automatic-map revision: PHP 8.5.12 feature test passed (1 test, 39 assertions), focused Pint passed, and Vite/export passed. Mobile/desktop screenshots: test-results/savy-google-map-390.png and test-results/savy-google-map-1280.png. The direct native-lazy iframe initially scored 78 on mobile; viewport-based automatic loading restored 98 without requiring a click.
- Remote static-preview verifier: passed for all five routes, styles/scripts/images, noindex, robots, and private-path denial. Vercel returns 403 for /vendor/autoload.php; /.env, /admin and /composer.json return 404. The verifier now accepts either denial response.
- Canonical: https://savy-nails-spa.vercel.app/; X-Robots-Tag: noindex, nofollow; X-Content-Type-Options: nosniff.
- Logo: the client-provided wordmark is background-extracted, tinted gold on light surfaces, white on the hero, and served through responsive WebP variants in the header, hero and footer.
- Favicon: all five pages declare versioned gold/white S icons (SVG, ICO, PNG, Apple Touch) and the SAVY web manifest; the root favicon also replaces the legacy K. Local and production favicon/link/manifest size verification passed. PHP 8.5.12 SEO audit/export and production build passed; composer quality stopped at the same eight unrelated existing Pint failures.
- Social preview: all five routes reference the warm nude-and-gold manicure WebP at `/storage/media/4/1536.webp`; Open Graph/Twitter metadata reports `image/webp`, 1536×1024, and descriptive alt text.
- Delivery cache audit: 29 raster responses across the five routes were `image/webp`; raster assets return one-day browser caching and one-year Vercel edge caching; hashed `/build/assets` return one-year immutable caching; conditional requests returned `304`.
- Motion smoke check: desktop and 390px static previews reported no console errors or horizontal overflow; the sticky-header state, scroll progress, reveal observer and logo loading all passed.
- Mobile floating controls: Facebook, Instagram and Back to top use inline SVG icons in a safe-area-aware stack with 44px targets. Removed the unnecessary 72px body padding; safe-area space belongs inside the copyright strip. Chromium mobile verification reached the full copyright and all three controls on all five routes at 440×956 and 440×640, including Back to top. WebKit was unavailable locally.
- The client's cropped mobile view was reproduced in the existing Chrome DevTools iPhone frame: it extended below the application window. Selecting Zoom → Fit to window restored the complete frame without changing website layout.
- Local production build and SEO audit: passed. Earlier full composer quality remains blocked by pre-existing Pint violations documented in SAVY_DESIGN.md; this deployment did not claim a clean full backend suite.

To update: build assets, set SITE_URL to the public URL, run starter:seo-audit and starter:export-demo, inspect the generated public artifact, deploy using the static configuration, then rerun `npm run verify:preview -- https://savy-nails-spa.vercel.app` and `node scripts/verify-delivery.mjs https://savy-nails-spa.vercel.app`. Push source changes with git push savy main. Keep deployment credentials and generated artifacts outside Git.

Rollback: promote the immutable deployment above from the Vercel project dashboard. This static deployment has no remote application database to roll back.
