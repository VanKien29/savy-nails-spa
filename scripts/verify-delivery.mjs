import {chromium} from '@playwright/test';
import assert from 'node:assert/strict';
import {promises as fs} from 'node:fs';

const origin = process.argv[2] || 'https://savy-nails-spa.vercel.app';
const browser = await chromium.launch();
const report = {origin, routes: [], rasterImages: [], cacheChecks: [], checkedAt: new Date().toISOString()};
try {
    const context = await browser.newContext();
    const page = await context.newPage();
    const images = new Map();
    page.on('response', response => {
        const url = new URL(response.url());
        if (url.origin !== origin) return; // Google's map assets are managed by Google.
        const type = response.headers()['content-type'] || '';
        if (type.startsWith('image/') && !type.includes('svg') && !type.includes('icon')) {
            images.set(url.href, {url: url.href, type, status: response.status()});
        }
    });
    for (const route of ['/', '/about-us', '/services', '/gallery', '/contact-us']) {
        const response = await page.goto(origin + route);
        assert.equal(response.status(), 200);
        await page.locator('img[src]').evaluateAll(async images => {
            images.forEach(image => image.loading = 'eager');
            await Promise.all(images.map(image => image.decode()));
        });
        // Exercise CSS backgrounds below the fold as well as the main image.
        await page.evaluate(async () => {
            for (const element of document.querySelectorAll('section, footer')) {
                element.scrollIntoView();
                await new Promise(resolve => requestAnimationFrame(resolve));
            }
        });
        const shareImage = await page.locator('meta[property="og:image"]').getAttribute('content');
        if (shareImage) {
            const share = await page.request.get(shareImage);
            assert.equal(share.status(), 200);
            assert.match(share.headers()['content-type'], /^image\/webp/);
        }
        const html = await page.request.get(origin + route);
        assert.match(html.headers()['cache-control'], /max-age=0/);
        report.routes.push({route, status: 200, htmlRevalidates: true});
    }
    assert.ok(images.size > 0);
    for (const item of images.values()) {
        assert.equal(item.status, 200, item.url);
        assert.match(item.type, /^image\/webp/, item.url);
        const image = await page.request.get(item.url);
        assert.match(image.headers()['cache-control'], /max-age=86400/, item.url);
        const etag = image.headers().etag;
        assert.ok(etag, item.url);
        const conditional = await page.request.get(item.url, {headers: {'If-None-Match': etag}});
        assert.equal(conditional.status(), 304, item.url);
        report.rasterImages.push({...item, cache: image.headers()['cache-control'], conditionalStatus: 304});
    }
    const hashedAssets = await page.locator('link[rel=stylesheet], script[src]').evaluateAll(elements => elements.map(element => element.href || element.src));
    for (const url of hashedAssets) {
        const response = await page.request.get(url);
        assert.match(response.headers()['cache-control'], /max-age=31536000.*immutable/);
        report.cacheChecks.push({url, cache: response.headers()['cache-control']});
    }
    await fs.mkdir('test-results', {recursive: true});
    await fs.writeFile('test-results/delivery-audit.json', JSON.stringify(report, null, 2));
    console.log(JSON.stringify({routes: report.routes.length, webpImages: report.rasterImages.length, cachedHashedAssets: report.cacheChecks.length, conditionalRequests: '304 PASS'}, null, 2));
} finally {
    await browser.close();
}
