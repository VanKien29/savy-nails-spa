# Savy reference design — 1 November 2026

Public pages now use a separate layout, stylesheet and JavaScript entry, rebuilt from the supplied reference at https://avatarnailsspasanramon.com/.

Reference pages inspected in the browser: Home, About Us, Services, Gallery and Contact Us.

Implemented:

- Cream sticky navigation with five links, active underline and framed gold booking/contact action.
- Full-width photographic hero with botanical circle, white salon wordmark and location line.
- Gold-overlay About section, floral service background, two-column menu with dotted price leaders.
- Rounded About cards, three-column bordered gallery, carousel, lightbox, FAQ and reveal effects.
- Large inner-page title banners, shared three-column footer and contact-only appointment actions.
- Inner-page banners use new black-manicure/emerald photographs, a dark green/charcoal gradient and warm-white headings. The ivory wash has been removed; these above-the-fold images load eagerly with responsive variants.
- Playfair Display, Libre Franklin and Dancing Script fonts served locally.
- Responsive layouts at 1280, 768 and 390 pixels, reduced motion and keyboard controls.

Intentional content differences: Savy branding, address, telephone, hours, social links and PDF service menu replace the reference salon's data. Contact actions replace its form/online scheduler. The testimonial-shaped section contains Savy introduction copy rather than unverified customer reviews. The footer map panel links to the supplied Google Maps listing.

Image inputs in resources/media/savy are taken from the user-requested reference's /wp-content/uploads/2024/04/ directory; savy-logo.png is client-provided. Nail/spa photos are reference imagery and are not represented as Savy customer photographs. All raster files pass through npm run media:optimize. Background files are capped at their 1000px source resolution; no upscaling is performed.

New banner sources (stock inspiration, not salon/customer photographs):

- banner-dark-manicure.jpg: de Aura, https://unsplash.com/photos/womans-hand-with-long-black-stiletto-nails-Y5P9SyEEQWM (Unsplash License).
- banner-emerald-nails.jpg: https://www.pexels.com/photo/close-up-photo-of-a-person-s-fingernails-with-black-manicure-8259423/ (Pexels free stock photo).
- Banner sources are downloaded at 1920px and optimized to responsive WebP variants. Dark overlay and white text separate the banners from surrounding cream sections.

Validation:

- npm run media:optimize: PASS.
- npm run build: PASS.
- Playwright Savy suite: PASS, 15 route/viewport combinations plus menu/contact/lightbox/keyboard controls; no failed network responses, broken images or horizontal overflow.
- starter:check, starter:seo-audit and starter:media-audit: PASS.
- composer quality: FAIL at existing Pint formatting violations in eight unchanged starter/test files; Composer validation/audit and PHP syntax passed. Focused Savy seeder Pint passed. Backend feature suite was not rerun for this template replacement.
- starter:export-demo: PASS, all five routes exported. An additional static preview server launch was rejected by automatic approval review without a stated reason; live static-preview browser checks were not executed.
- Screenshots: test-results/savy-{1280,768,390}-{home,about-us,services,gallery,contact-us}.png (local ignored artifacts).

Reproduction: install Composer/npm dependencies, configure the local MySQL database, run npm run media:optimize, migrate, seed SavyNailsSeeder, and build. Use PHP 8.5.12 or later within the project's 8.5 baseline.
