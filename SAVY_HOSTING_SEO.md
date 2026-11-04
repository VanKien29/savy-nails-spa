# SAVY hosting SEO configuration

The Vercel site is a static preview and intentionally retains meta/header `noindex, nofollow` plus a blocking robots.txt. Do not remove those restrictions to improve a preview score. The client plans to launch on PHP hosting under a future domain.

On the hosting installation, configure these private environment values using the real domain:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR_REAL_DOMAIN
SITE_URL=https://YOUR_REAL_DOMAIN
STARTER_PREVIEW=false
STARTER_SHOWCASE=false
SESSION_SECURE_COOKIE=true
```

Use PHP >=8.5.12 <8.6 for web and CLI. Keep the existing database, APP_KEY and media when transferring the installation. Supply the host's database/session/cache settings privately. Do not run a sample/client seeder over an existing salon menu. Follow `docs/DEPLOY_HOSTING.md` for the private application/public document-root layout, migrations, storage, backups and permissions.

Deploy the Laravel application with document root `public/`, rather than uploading `dist/preview`. The preview export always sets noindex, blocks robots and omits sitemap, regardless of production settings. Do not reuse the Vercel preview headers on hosting. No new public exporter or indexing bypass has been added.

After the host/domain/HTTPS is ready:

```sh
php artisan optimize:clear
php artisan optimize
php artisan starter:check --production
php artisan starter:seo-audit
```

Verify the actual served HTML and response headers: public published pages should say `index, follow` and should have no X-Robots-Tag noindex injected by the host. Ensure the five public Page records have `noindex=false`. `/robots.txt` must allow public pages and reference the real HTTPS `/sitemap.xml`. Sitemap and all canonical/OG/schema URLs must use the final domain. Unpublished/private/admin pages remain excluded. Test trailing-slash/old-URL redirects and a real 404, then submit the final sitemap through the owner's Search Console.

Rerun Google PageSpeed mobile/desktop on the actual host after DNS and TLS settle. Hosting response time and network conditions affect performance; a local lab score is not evidence of the final host's speed or a ranking guarantee.

The client explicitly requested keeping the supplied San Ramon map while the website address remains Walla Walla. That mismatch is retained and should be revisited before a local-business SEO campaign. Schema syntax validation does not certify the factual match of the listing.

Interactive map: the real Google embed loads automatically within 300px of the viewport, with reserved dimensions and no activation button. A noscript iframe supports disabled JavaScript; the external Maps link remains available. This supersedes the earlier click-to-load optimization at the client's request. Viewport loading prevents Google requests from competing with the hero image on initial load.
