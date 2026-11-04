import {test, expect} from '@playwright/test';

test('real Google map loads in the shared footer on desktop and mobile', async ({page}) => {
    for (const width of [1280, 390]) {
        await page.setViewportSize({width, height: 900});
        await page.goto('/contact-us');
        await page.locator('.av-map-card').scrollIntoViewIfNeeded();
        await expect(page.getByRole('button', {name: 'View interactive map'})).toHaveCount(0);
        await expect(page.locator('.av-map-card iframe')).toHaveAttribute('src', 'https://www.google.com/maps/embed?pb=!1m3!3m2!1m1!4s17343372594423126593');
        await expect(page.frameLocator('.av-map-card iframe').getByRole('link', {name: /Open in Maps/})).toBeVisible({timeout: 30000});
        await page.locator('.av-map-card').screenshot({path: 'test-results/savy-google-map-' + width + '.png'});
    }
});

test('all reference pages render across three viewport sizes', async ({page}) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('response', response => {if(response.status() >= 400) errors.push(response.status() + ' ' + response.url());});
    await page.emulateMedia({reducedMotion:'reduce'});
    for (const width of [1280,768,390]) {
        await page.setViewportSize({width,height:900});
        for(const route of ['/','/about-us','/services','/gallery','/contact-us']) {
            expect((await page.goto(route)).status()).toBe(200);
            await expect(page.locator('h1')).toHaveCount(1);
            await expect(page).toHaveTitle(/Savy Nails & Spa/);
            await expect(page.locator('link[href*="salon-"]')).toHaveCount(0);
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBeTruthy();
            await page.locator('.av-footer').scrollIntoViewIfNeeded();
            await expect(page.locator('.av-footer')).toContainText('09:00 AM - 07:00 PM');
            await page.evaluate(async () => {
                const images = [...document.images].filter(img => !img.closest('dialog'));
                images.forEach(img => img.loading = 'eager');
                await Promise.all(images.map(img => img.decode().catch(() => {})));
            });
            expect(await page.locator('img:not(dialog img)').evaluateAll(images => images.filter(img => !img.complete || !img.naturalWidth).map(img=>img.src))).toEqual([]);
            await page.evaluate(() => scrollTo(0,0));
            await page.screenshot({path:'test-results/savy-' + width + '-' + (route==='/'?'home':route.slice(1)) + '.png',fullPage:true});
        }
    }
    expect(errors).toEqual([]);
});

test('menu, contact, lightbox and keyboard controls work',async ({page})=>{
    await page.goto('/services');
    await expect(page.locator('[data-menu-category]')).toHaveCount(4);
    await expect(page.locator('[data-service-row]')).toHaveCount(31);
    await expect(page.locator('[data-service-row]').filter({hasText:'Gel Overlay'})).toContainText('$....');
    await page.goto('/contact-us');
    await expect(page.locator('main form')).toHaveCount(0);
    await expect(page.locator('main')).toContainText('634 S 9th Ave, Walla Walla, WA 99362');
    await expect(page.locator('main a[href^="tel:"]')).toHaveCount(2);
    await page.goto('/gallery');
    await expect(page.locator('.av-gallery-photo')).toHaveCount(12);
    await page.locator('.av-gallery-photo').first().click();
    await expect(page.locator('dialog')).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(page.locator('dialog')).not.toBeVisible();
    await expect(page.locator('.av-gallery-photo').first()).toBeFocused();
    await page.goto('/');
    await page.locator('summary').first().click();
    await expect(page.locator('details').first()).toHaveAttribute('open','');
    await page.setViewportSize({width:390,height:844});
    await page.getByRole('button',{name:'Toggle navigation'}).click();
    await expect(page.locator('.av-nav')).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(page.locator('.av-nav-toggle')).toBeFocused();
    expect((await page.goto('/booking')).status()).toBe(404);
});
