import {test,expect} from '@playwright/test';

async function dragLeft(page,rail){
    const box=await rail.boundingBox();
    await page.mouse.move(box.x+box.width-45,box.y+box.height/2);
    await page.mouse.down();
    await page.mouse.move(box.x+40,box.y+box.height/2,{steps:14});
    await page.mouse.up();
}

test('mobile filter rails drag without activating a category or trapping page scroll',async({page})=>{
    await page.setViewportSize({width:390,height:844});await page.emulateMedia({reducedMotion:'reduce'});await page.goto('/services');
    const rail=page.getByRole('navigation',{name:'Service categories'});
    await dragLeft(page,rail);await expect.poll(()=>rail.evaluate(el=>el.scrollLeft)).toBeGreaterThan(200);
    await expect(page.locator('[data-service-category="nails"]')).toHaveAttribute('aria-current','true');
    await page.locator('[data-service-category="kids"]').click();await expect(page.locator('[data-menu-category="kids"]')).toBeVisible();
    await page.goto('/services');await rail.focus();await page.keyboard.press('ArrowRight');await expect.poll(()=>rail.evaluate(el=>el.scrollLeft)).toBeGreaterThan(100);
    await rail.hover();await page.mouse.wheel(0,600);await expect.poll(()=>page.evaluate(()=>scrollY)).toBeGreaterThan(200);
});

test('native touchscreen swipe reveals the last service category',async({browser,baseURL})=>{
    const context=await browser.newContext({viewport:{width:390,height:844},isMobile:true,hasTouch:true,reducedMotion:'reduce'});
    try{
        const page=await context.newPage();await page.goto(baseURL+'/services');const rail=page.getByRole('navigation',{name:'Service categories'});const box=await rail.boundingBox();
        const cdp=await context.newCDPSession(page);const y=Math.round(box.y+box.height/2);
        await cdp.send('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[{x:335,y}]});
        for(let step=1;step<=12;step++){await cdp.send('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x:335-step*24,y}]});await new Promise(resolve=>setTimeout(resolve,16));}
        await cdp.send('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});
        await expect.poll(()=>rail.evaluate(el=>el.scrollLeft)).toBeGreaterThan(200);await expect(page.locator('[data-service-category="nails"]')).toHaveAttribute('aria-current','true');
        await cdp.send('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[{x:335,y}]});
        for(let step=1;step<=10;step++){await cdp.send('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x:335-step*24,y}]});await new Promise(resolve=>setTimeout(resolve,16));}
        await rail.evaluate(el=>{
            el.dataset.testScrollState='moving';
            el.addEventListener('scrollend',()=>{el.dataset.testScrollState='settled';},{once:true});
        });
        await cdp.send('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});
        // A tap during native momentum only stops scrolling; wait for the rail to settle.
        await expect(rail).toHaveAttribute('data-test-scroll-state','settled');
        const last=page.locator('[data-service-category="kids"]');await expect.poll(async()=>{const item=await last.boundingBox();return item.x+item.width<=391;}).toBeTruthy();await last.tap();await expect(page.locator('[data-menu-category="kids"]')).toBeVisible();
    }finally{await context.close();}
});

test('contact map has an allowed Google embed and an outbound directions fallback',async({page})=>{
    const response=await page.goto('/contact-us');expect(response.headers()['content-security-policy']).toContain('https://www.google.com/maps/embed');
    const frame=page.getByTitle(/^Google Maps/);await expect(frame).toHaveAttribute('src',/^https:\/\/www\.google\.com\/maps\/embed\?pb=/);await expect(frame).toHaveAttribute('loading','lazy');
    await expect(page.locator('.ks-location-graphic')).toHaveCount(0);await expect(page.getByRole('link',{name:'Open in Google Maps',exact:false})).toHaveAttribute('href','https://maps.app.goo.gl/rZcnSx2muwHrotw88');
    for(const width of [390,1280]){await page.setViewportSize({width,height:844});const box=await frame.boundingBox();expect(box.width).toBeGreaterThan(250);expect(box.height).toBeGreaterThan(280);expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();}
});
