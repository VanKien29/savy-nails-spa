import {test,expect} from '@playwright/test';

const bookingUrl='https://lamournails6484.simplepos.us/new-home';

test('customer design is consistent across desktop and mobile pages',async({page})=>{
    await page.emulateMedia({reducedMotion:'reduce'});
    for(const width of [1440,390]){
        await page.setViewportSize({width,height:900});
        for(const route of ['/','/about-us','/services','/gallery','/contact-us']){
            await page.goto(route);
            await expect(page.locator('svg[data-icon="arrow"],svg[data-icon="arrow-up"],.ks-back-top')).toHaveCount(0);
            await expect(page.locator('footer h2')).toHaveText(['Business Hours']);
            const buttons=page.getByRole('link',{name:'Booking',exact:true,includeHidden:true});
            expect(await buttons.count()).toBeGreaterThanOrEqual(2);
            for(const button of await buttons.all())await expect(button).toHaveAttribute('href',bookingUrl);
            if(route==='/'){
                await expect(page.locator('main>section')).toHaveCount(7);
                await expect(page.locator('.kl-questions,.kl-faqs,.kl-look figcaption,.kl-lookbook .ks-center-action')).toHaveCount(0);
                await expect(page.locator('[data-slide="1"]')).toBeVisible();
            }
            if(route==='/services'){
                const kids=page.locator('[data-menu-category="kids"]');
                await expect(kids.locator('h2')).toHaveText('Kid’s Services(For children who are under 12 years old)');
                await expect(kids.locator('[data-service-row] p')).toHaveCount(0);
            }
            if(route==='/gallery'){
                await expect(page.locator('.ks-gallery-item figcaption,.ks-gallery-filters,.ks-image-source')).toHaveCount(0);
                await expect(page.locator('[data-lightbox],#image-lightbox,.ks-gallery-disclosure')).toHaveCount(0);
                expect(await page.locator('.ks-gallery-item img').count()).toBeGreaterThan(0);
                await page.locator('.ks-gallery-item img').first().click();
                await expect(page.locator('dialog:visible')).toHaveCount(0);
            }
            if(route==='/contact-us'){
                await expect(page.locator('main form,.ks-contact-form-card,#contact-dialog')).toHaveCount(0);
                const photo=page.locator('.ks-contact-photo img');
                await photo.scrollIntoViewIfNeeded();
                await expect(photo).toBeVisible();
                await expect.poll(()=>photo.evaluate(img=>img.complete&&img.naturalWidth>0)).toBeTruthy();
            }
            expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();
        }
    }
    await page.route(bookingUrl,route=>route.fulfill({status:200,contentType:'text/html',body:'<h1>Booking destination verified</h1>'}));
    await page.goto('/');
    await page.getByRole('button',{name:'Toggle navigation'}).click();
    await page.locator('#mobile-nav').getByRole('link',{name:'Booking',exact:true}).click();
    await expect(page).toHaveURL(bookingUrl);
});
