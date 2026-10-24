import {test,expect} from '@playwright/test';

test('contact map has an allowed Google embed and an outbound directions fallback',async({page})=>{
    const response=await page.goto('/contact-us');expect(response.headers()['content-security-policy']).toContain('https://www.google.com/maps/embed');
    const frame=page.getByTitle(/^Google Maps/);await expect(frame).toHaveAttribute('src',/^https:\/\/www\.google\.com\/maps\/embed\?pb=/);await expect(frame).toHaveAttribute('loading','lazy');
    await expect(page.locator('.ks-location-graphic')).toHaveCount(0);await expect(page.getByRole('link',{name:'Open in Google Maps',exact:false})).toHaveAttribute('href','https://maps.app.goo.gl/rZcnSx2muwHrotw88');
    for(const width of [390,1280]){await page.setViewportSize({width,height:844});const box=await frame.boundingBox();expect(box.width).toBeGreaterThan(250);expect(box.height).toBeGreaterThan(280);expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();}
});
