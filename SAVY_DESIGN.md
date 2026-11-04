# Savy reference design — 1 November 2026

Public pages now use a separate layout, stylesheet and JavaScript entry, rebuilt from the supplied reference at https://avatarnailsspasanramon.com/.

Reference pages inspected in the browser: Home, About Us, Services, Gallery and Contact Us.

Implemented:

- Cream sticky navigation with five links, active underline and framed gold booking/contact action.
- Full-width photographic hero with botanical circle, white salon wordmark and location line.
- Gold-overlay About section, floral service background, two-column menu with dotted price leaders.
- Rounded About cards, three-column bordered gallery, carousel, lightbox, FAQ and reveal effects.
- Large inner-page title banners, shared three-column footer and contact-only appointment actions.
- Inner-page banners use the existing warm bronze spa and nude/gold manicure artwork (the-k/luxury-ritual.png, luxury-hero.png and luxury-look-gold.png), a chocolate-brown gradient and warm-white headings. These replace the off-palette green/black stock photos; above-the-fold images load eagerly with responsive variants.
- Playfair Display, Libre Franklin and Dancing Script fonts served locally.
- SAVY favicons use a font-independent outlined S in warm white on the site's gold background. All five routes declare versioned SVG/ICO/32px PNG, 180px Apple Touch and a SAVY-named manifest with 192/512px icons. Root automatic favicon requests also receive the S icon. Rebuild with `npm run icons:build`; PNG/ICO are intentional browser/app icon compatibility formats.
- Responsive layouts at 1280, 768 and 390 pixels, reduced motion and keyboard controls.
- Reference-style motion pass: smooth anchor scrolling with sticky-header offset, scroll progress indicator, shrinking header shadow, scroll-triggered reveals with staggered timing, subtle hero parallax, button/card/image hover lift and page-banner zoom. All enhanced motion is disabled or made instant for `prefers-reduced-motion`.
- SEO/performance pass (4 November): initial hero zoom/parallax and above-fold reveal delays are retired so LCP is immediately visible; below-fold motion and hover remain. English pages use Latin font subsets. Only the LCP photo receives high image priority, rather than logos. Bronze text/buttons have stronger contrast. The real Google map loads after “View interactive map”, and the outbound link works without JavaScript. The client confirmed keeping the supplied map and retaining noindex on Vercel until the later hosting launch. See SAVY_HOSTING_SEO.md.

Intentional content differences: Savy branding, address, telephone, hours, social links and PDF service menu replace the reference salon's data. Contact actions replace its form/online scheduler. The testimonial-shaped section contains Savy introduction copy rather than unverified customer reviews. The footer now embeds the real Google Maps listing supplied by the client, with an outbound Maps link.

Google Maps embed: lazy iframe using the canonical maps/embed URL for CID 17343372594423126593. Frame permissions allow only Google's maps/embed path on the five configured Savy public routes; admin routes remain restricted. Verified the live map and location marker in the browser. Rerun SavyMapLinkSeeder to apply the embed setting on an existing installation.

Embed verification: SavyMapEmbedTest passed (1 test, 19 assertions) for all five public pages, admin frame restrictions and empty-setting fallback. The focused real-map browser check passed at 1280px and 390px; Google iframe controls rendered, and map screenshots were captured at test-results/savy-google-map-{1280,390}.png. Production asset build and focused Pint checks passed.

Image inputs in resources/media/savy are taken from the user-requested reference's /wp-content/uploads/2024/04/ directory; the transparent Savy wordmark is client-provided and is used in the header, hero and footer. Nail/spa photos are reference imagery and are not represented as Savy customer photographs. All raster files pass through npm run media:optimize. Background files are capped at their 1000px source resolution; no upscaling is performed.

Previous stock candidates (retained as source assets but no longer used by banners):

- banner-dark-manicure.jpg: de Aura, https://unsplash.com/photos/womans-hand-with-long-black-stiletto-nails-Y5P9SyEEQWM (Unsplash License).
- banner-emerald-nails.jpg: https://www.pexels.com/photo/close-up-photo-of-a-person-s-fingernails-with-black-manicure-8259423/ (Pexels free stock photo).
- Current banner artwork reuses already optimized responsive WebP variants from the repository. Bronze, champagne, warm brown and ivory match the site's gold/cream palette; the dark brown overlay keeps headings readable.
- Social previews use the dedicated nude-and-gold manicure image (`/storage/media/4/1536.webp`) with matching Open Graph/Twitter metadata on all five public routes. The image is served as WebP with explicit 1536×1024 dimensions and descriptive alt text.

Validation:

- npm run media:optimize: PASS.
- npm run build: PASS.
- Playwright Savy suite: PASS, 15 route/viewport combinations plus menu/contact/lightbox/keyboard controls; no failed network responses, broken images or horizontal overflow.
- starter:check, starter:seo-audit and starter:media-audit: PASS.
- composer quality: FAIL at existing Pint formatting violations in eight unchanged starter/test files; Composer validation/audit and PHP syntax passed. Focused Savy seeder Pint passed. Backend feature suite was not rerun for this template replacement.
- starter:export-demo: PASS, all five routes exported. An additional static preview server launch was rejected by automatic approval review without a stated reason; live static-preview browser checks were not executed.
- Live delivery audit after the latest deploy: PASS — 5 public routes, 29 raster image responses, all `image/webp`; hashed build assets use one-year immutable caching; raster assets use one-day browser caching plus one-year Vercel edge caching; conditional requests returned `304`.
- Public HTML uses `Cache-Control: public, max-age=0, must-revalidate` with a five-minute edge cache and stale-while-revalidate. Laravel settings/catalog/navigation caches remain scoped to public reads and are invalidated on content mutation; admin/private HTML is not cached.
- Screenshots: test-results/savy-{1280,768,390}-{home,about-us,services,gallery,contact-us}.png (local ignored artifacts).

Reproduction: install Composer/npm dependencies, configure the local MySQL database, run npm run media:optimize, migrate, seed SavyNailsSeeder, and build. Use PHP 8.5.12 or later within the project's 8.5 baseline.

Map destination update: the client-supplied URL now uses Google Maps CID 17343372594423126593. Run `php artisan db:seed --class=SavyMapLinkSeeder --no-interaction` to update an existing installation's map/Google social links without resetting its menu or other client content. New Savy seeds use the same URL constant. All five public routes were checked for the new href and absence of the old short link.
