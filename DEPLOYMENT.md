# Savy Nails & Spa — Vercel deployment

- Website: https://savy-nails-spa.vercel.app
- Project/account: vankien29/savy-nails-spa
- Deployed: 1 November 2026, Asia/Saigon.
- Source revision: 237e4d8 (latest website implementation at deployment time).
- Deployment: dpl_GodR7eu6Ca1GJACizu1LSxAVhq9j, status READY, target production.
- Immutable URL: https://savy-nails-fm2ucr0t6-vankien29.vercel.app
- Git repository: https://github.com/VanKien29/savy-nails-spa

Mode: static client preview, exported with PHP 8.5.12 from the local public Savy database. All five public pages, responsive media/fonts, gallery, FAQ, contact links and the Google Maps embed are included. Laravel/admin/database and online form persistence are not hosted on Vercel. Preview remains noindex by design; canonical points to the website URL above.

Only the rendered artifact and static Vercel config were deployed from tmp/savy-vercel. Laravel source, environment files, customer records and public.zip were excluded. The local project association now points to Savy rather than the former THE K Vercel project. Git-based automatic builds are not configured: new website content must be exported before publishing.

Verification against the deployed public URL:

- Vercel READY; HTTPS root and all four nested pages respond successfully.
- Remote Savy Playwright suite: 3 tests passed, covering desktop/tablet/mobile routes, image loading, no overflow, menu/prices, contact-only booking, lightbox, keyboard controls and the real Google map.
- Remote static-preview verifier: passed for all five routes, styles/scripts/images, noindex, robots, and private-path denial. Vercel returns 403 for /vendor/autoload.php; /.env, /admin and /composer.json return 404. The verifier now accepts either denial response.
- Canonical: https://savy-nails-spa.vercel.app/; X-Robots-Tag: noindex, nofollow; X-Content-Type-Options: nosniff.
- Local production build and SEO audit: passed. Earlier full composer quality remains blocked by pre-existing Pint violations documented in SAVY_DESIGN.md; this deployment did not claim a clean full backend suite.

To update: build assets, set SITE_URL to the public URL, run starter:seo-audit and starter:export-demo, inspect the generated public artifact, deploy using the static configuration, then rerun npm run verify:preview against the public URL. Push source changes with git push savy main. Keep deployment credentials and generated artifacts outside Git.

Rollback: promote the immutable deployment above from the Vercel project dashboard. This static deployment has no remote application database to roll back.
