import {test,expect} from '@playwright/test';

test('customer service menu keeps paired prices and detailed treatments readable',async({page})=>{
    await page.emulateMedia({reducedMotion:'reduce'});
    for(const width of [1440,390,360]){
        await page.setViewportSize({width,height:1000});
        await page.goto('/services');
        await expect(page.locator('[data-service-row]')).toHaveCount(55);
        const rows=page.locator('.ks-menu-group-paired [data-service-row]');
        await expect(rows).toHaveCount(5);
        await expect(rows.first().locator('.ks-menu-price-pair')).toContainText('$55+');
        await expect(rows.first().locator('.ks-menu-price-pair')).toContainText('$45+');
        await expect(rows.nth(1).locator('.ks-menu-price-pair>strong').last()).toBeEmpty();
        await expect(page.locator('.ks-menu-price-headings')).toHaveText('Full SetFill-in');
        const columns=await rows.evaluateAll(items=>items.map(item=>[...item.querySelectorAll('.ks-menu-price-pair>strong')].map(price=>price.getBoundingClientRect().right)));
        for(const column of columns){expect(Math.abs(column[0]-columns[0][0])).toBeLessThan(1);expect(Math.abs(column[1]-columns[0][1])).toBeLessThan(1);}
        const brow=page.locator('[data-menu-category="brows-lashes"]');
        await brow.locator('.ks-menu-brow-photos').scrollIntoViewIfNeeded();
        for(const img of await brow.locator('.ks-menu-brow-photos img').all())await expect.poll(()=>img.evaluate(image=>image.complete&&image.naturalWidth>0)).toBeTruthy();
        await page.locator('[data-menu-category="nails"]').screenshot({path:`test-results/client-menu-nails-${width}.png`});
        await page.locator('.ks-menu-beauty-group').first().screenshot({path:`test-results/client-menu-brows-${width}.png`});
        const signature=page.locator('[data-service-row]').filter({hasText:'The K Signature Royal Pedicure'});
        await expect(signature.locator('li')).toHaveCount(14);
        await signature.screenshot({path:`test-results/client-menu-pedicure-${width}.png`});
        await expect(page.locator('[data-menu-category="kids"]')).toContainText('For children who are under 12 years old');
        expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();
    }
});
