import {test,expect} from '@playwright/test';

test('first-visit icons keep their SVG shapes while web fonts are delayed',async({browser,baseURL})=>{
    for(const path of ['/','/about-us','/services','/gallery','/contact-us']){
        const context=await browser.newContext({viewport:{width:390,height:844},reducedMotion:'reduce'});
        let releaseFonts;
        const fontGate=new Promise(resolve=>{releaseFonts=resolve;});
        await context.route('**/*',async route=>{
            if(route.request().resourceType()==='font')await fontGate;
            await route.continue();
        });
        try{
            const page=await context.newPage();
            await page.goto(baseURL+path,{waitUntil:'domcontentloaded'});
            const shapes=()=>page.locator('svg[data-icon]').evaluateAll(icons=>icons.map(icon=>({
                name:icon.dataset.icon,shape:icon.innerHTML,viewBox:icon.getAttribute('viewBox'),
                width:getComputedStyle(icon).width,height:getComputedStyle(icon).height,
            })));
            const before=await shapes();
            expect(before.length).toBeGreaterThan(0);
            expect(before.map(icon=>icon.name)).toEqual(expect.arrayContaining(['facebook','instagram','close']));
            expect(before.every(icon=>icon.shape.includes('<path')||icon.shape.includes('<rect')||icon.shape.includes('<circle'))).toBeTruthy();
            expect(await page.locator('body').textContent()).not.toMatch(/[↗↖→←↑↓✧]/u);
            releaseFonts();
            await page.evaluate(()=>document.fonts.ready);
            expect(await shapes()).toEqual(before);
            for(const href of await page.locator('link[rel=icon],link[rel=apple-touch-icon],link[rel=manifest]').evaluateAll(links=>links.map(link=>link.href))){
                const response=await context.request.get(href);expect(response.status(),href).toBe(200);expect((await response.body()).length,href).toBeGreaterThan(50);
            }
            const ico=await context.request.get(baseURL+'/favicon.ico');expect(ico.status()).toBe(200);expect([...(await ico.body()).subarray(0,4)]).toEqual([0,0,1,0]);
            const svg=await context.request.get(baseURL+'/assets/the-k/favicon.svg');expect(await svg.text()).toContain('<path');expect(await svg.text()).not.toContain('<text');
        }finally{releaseFonts();await context.close();}
    }
});
