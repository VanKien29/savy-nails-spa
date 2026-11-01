import {test, expect} from '@playwright/test';

test.describe('Savy Nails & Spa client', () => {
    test('public pages render the Savy brand and contact-only booking flow', async ({page}) => {
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.emulateMedia({reducedMotion: 'reduce'});

        for (const route of ['/', '/about-us', '/services', '/gallery', '/contact-us']) {
            const response = await page.goto(route);
            expect(response.status()).toBe(200);
            await expect(page.locator('h1')).toHaveCount(1);
            await expect(page).toHaveTitle(/Savy Nails & Spa/i);
            await expect(page.locator('body')).toContainText('Savy');
            await expect(page.getByRole('contentinfo')).toContainText('Business Hours');
            await expect(page.getByRole('contentinfo')).toContainText('09:00 AM');
            await expect(page.getByRole('contentinfo')).toContainText('05:00 PM');
            await expect(page.locator('body')).not.toContainText('THE K');
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBeTruthy();
        }

        await page.goto('/');
        await expect(page.locator('.savy-home')).toBeVisible();
        for (const section of ['.savy-hero', '.savy-service-strip', '.savy-about', '.savy-collection', '.savy-signature', '.savy-gallery', '.savy-visit', '.savy-cta']) {
            await expect(page.locator(section)).toBeVisible();
        }
        await expect(page.getByRole('link', {name: 'Contact to Book', exact: true}).first()).toHaveAttribute('href', '/contact-us');
        await expect(page.getByRole('link', {name: 'View the Menu', exact: true})).toHaveAttribute('href', '/services');

        await page.goto('/services');
        await expect(page.locator('[data-menu-category]')).toHaveCount(4);
        await expect(page.locator('[data-service-row]')).toHaveCount(31);
        await expect(page.locator('[data-service-row]').filter({hasText: 'Gel Overlay'})).toContainText('$....');
        await expect(page.locator('[data-service-row]').filter({hasText: 'Cheeks'})).toContainText('$....');
        await expect(page.locator('main form')).toHaveCount(0);

        await page.goto('/contact-us');
        await expect(page.locator('main form')).toHaveCount(0);
        await expect(page.locator('main')).toContainText('634 S 9th Ave, Walla Walla, WA 99362');
        await expect(page.locator('main')).toContainText('509-529-7289');
        await expect(page.locator('main a[href="https://www.facebook.com/profile.php?id=61557425844879"]')).toHaveCount(1);
        await expect(page.locator('main a[href^="https://www.instagram.com/savynailsspa"]')).toHaveCount(1);
        const retired = await page.goto('/booking');
        expect(retired.status()).toBe(404);
        expect(errors).toEqual([]);
    });

    test('mobile navigation remains keyboard reachable without horizontal overflow', async ({page}) => {
        await page.setViewportSize({width: 390, height: 844});
        await page.emulateMedia({reducedMotion: 'reduce'});
        await page.goto('/');
        await page.getByRole('button', {name: 'Toggle navigation'}).click();
        await expect(page.locator('#mobile-nav')).toHaveClass(/is-open/);
        await expect(page.locator('#mobile-nav').getByRole('link', {name: 'Contact', exact: true})).toHaveAttribute('href', '/contact-us');
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBeTruthy();
    });
});
