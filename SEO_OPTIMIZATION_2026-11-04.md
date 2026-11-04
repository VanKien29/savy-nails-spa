# SAVY SEO/performance optimization — 4 November 2026

Client decisions: retain Vercel as a noindex preview until a later PHP hosting launch; retain the supplied map. No indexing requests or public production-mode deployment were made.

## External result on the final live build

Google PageSpeed report, home page, 17:50 GMT+7:
https://pagespeed.web.dev/analysis/https-savy-nails-spa-vercel-app/2wu3x4ugam?form_factor=mobile

| Category | Mobile | Desktop |
|---|---:|---:|
| Performance | 98 | 100 |
| Accessibility | 100 | 100 |
| Best Practices | 100 | 100 |
| SEO | 69 | 69 |

Mobile LCP 1.8 s (previous audit 5.9 s), FCP 0.8 s, TBT 0 ms, CLS 0. Mobile is a simulated Moto G Power / slow 4G lab run. CrUX has no field data. These are home-page lab measurements, not rankings or a guarantee for every future run/hosting provider.

The target above 95 is met for the three publicly measurable preview categories. Preview SEO intentionally remains below the requested threshold because `noindex` is required by the client's hosting plan. It would be inaccurate to report all four public scores above 95.

## Implementation

- Latin font subsets for the English site reduce CSS/font requests.
- Hero paints immediately without initial zoom/parallax or above-fold reveal hiding. Below-fold reveal, hover, smooth anchors, gallery and keyboard interactions remain.
- High image fetch priority reserved for above-fold photos, not the header/hero logos.
- Text on cream backgrounds uses dark bronze (#68481f, about 6.19:1 against #eaddca); white CTA/copyright surfaces use #805f2f. Desktop navigation contrast now passes PageSpeed.
- Google Maps loads on visitor request through a 44px button; iframe space stays reserved, and the external map link remains usable without scripts. Client-requested map CID remains unchanged. No user-agent-specific optimization or score-dependent content is used.
- Shared SeoService and preview indexing protections remain intact. Hosting launch configuration and final-domain checks are in SAVY_HOSTING_SEO.md.

## Verification

- npm run build: PASS.
- PHP 8.5.12 starter:seo-audit: PASS on all five pages in normal local mode and with production indexing configuration (STARTER_PREVIEW=false).
- Public indexing rules exercised on a loopback-only Laravel test server: all five pages emit index/follow; no noindex response header; loaded stylesheets return 200; robots allows public crawling and refers to a sitemap containing five URLs. Real future hosting/domain has not been tested.
- Local Lighthouse on the fully styled loopback test application: SEO 100, Accessibility 100, Best Practices 100, Performance 93 (LCP 3.1 s). This development-server speed is not the external hosting result and does not replace the PageSpeed measurement above. An earlier production-environment localhost run forced HTTPS asset URLs on the HTTP development server; it was invalid and was discarded.
- SavyMapEmbedTest: PASS, 1 test / 29 assertions, isolated test database. Confirms no Google iframe until requested and preserved scoped CSP permissions.
- Savy browser suite against live Vercel: PASS, 3 tests, including 15 route/viewport combinations, real map activation, content/images/overflow, menu/contact, gallery, Escape and mobile menu.
- Focused Pint on changed PHP test: PASS. Full composer quality: dependency validation/audit and PHP syntax passed, then stopped at the same eight pre-existing formatting failures in unrelated files. Full backend suite is not claimed passed.
- No full PageSpeed matrix for every route or real-user Core Web Vitals assessment is claimed.

Deployment: dpl_ESinVimzrFXFM6n12Y4j45wKZ8KF, READY, https://savy-nails-emfhenb3z-vankien29.vercel.app ; public alias https://savy-nails-spa.vercel.app .
