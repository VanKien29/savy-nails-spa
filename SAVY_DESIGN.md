# Savy client implementation

Five public pages use `salon.pages.savy-reference`, including the compatibility template `salon-home`. Active client assets are `resources/css/savy-reference.css`, `resources/js/savy-reference.js`, `resources/media/savy/` and `public/assets/savy/`.

Preserved the approved white/gold layout, photo composition, image banners, smooth scrolling, below-fold reveals, gallery/lightbox and social/back-to-top icons. The transparent client wordmark uses responsive WebP; the S favicon has SVG/ICO/PNG fallbacks. Retained editorial photographs use Savy filenames: banner-about.png, banner-services.png, banner-gallery.png, social-manicure.png. Obsolete client seeders, public layouts, assets and tests were removed.

The approved Savy menu has 31 services in four categories and six intentional pending-price placeholders. Re-running the seed preserves existing IDs, prices, descriptions and visibility. Existing lead/settings/SEO/media/admin services remain shared.

Appointments use phone/social only. Keep the supplied Google map, Facebook/Instagram links and business hours. Maps loads automatically within 300px of the viewport; noscript iframe and external link remain. Above-fold hero motion was removed for LCP; reduced-motion support and below-fold effects remain.

Local runtime and tests use separate schemas: savy_nails_spa and savy_nails_spa_testing. The previous database was backed up before cloning. See SAVY_CLEANUP.md for safe hosting migration and real SEO evidence.
