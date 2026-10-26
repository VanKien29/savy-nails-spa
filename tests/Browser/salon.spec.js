import {test,expect} from '@playwright/test';
import {randomUUID} from 'node:crypto';
import {spawnSync} from 'node:child_process';
const email=`browser-${randomUUID()}@example.invalid`;
function fixture(action){const result=spawnSync(process.env.STARTER_PHP||'php',['tests/Browser/fixture.php'],{input:JSON.stringify({action,email}),encoding:'utf8'});if(result.status!==0)throw new Error(result.stderr);}
test.afterAll(()=>fixture('cleanup'));
async function loadImages(page){await page.locator('img[loading=lazy]').evaluateAll(imgs=>imgs.forEach(img=>img.loading='eager'));await expect.poll(()=>page.locator('img[src]').evaluateAll(imgs=>imgs.every(img=>img.complete&&img.naturalWidth>0))).toBeTruthy();await page.evaluate(()=>document.fonts.ready);}
test('five pages, responsive layout, assets, links and English metadata',async({page})=>{
    const errors=[];page.on('pageerror',error=>errors.push(error.message));
    for(const path of ['/','/about-us','/services','/gallery','/contact-us']){
        const response=await page.goto(path);expect(response.status()).toBe(200);await expect(page.locator('h1')).toHaveCount(1);await expect(page).toHaveTitle(/THE K/);await expect(page.locator('html')).toHaveAttribute('lang','en');await loadImages(page);
        for(const width of [360,390,768,1280,1440]){await page.setViewportSize({width,height:900});expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`${path} overflow at ${width}`).toBeTruthy();}
        await loadImages(page);await page.locator('img[src]').evaluateAll(imgs=>Promise.all(imgs.map(img=>img.decode().catch(()=>{}))));if(path==='/contact-us')await expect(page.locator('main')).toContainText('07:00 PM');
        for(const asset of await page.locator('script[src],link[rel=stylesheet]').evaluateAll(els=>els.map(el=>el.src||el.href)))expect((await page.request.get(asset)).status()).toBe(200);
        await page.emulateMedia({reducedMotion:'reduce'});const filename=path==='/'?'home':path.slice(1);await page.screenshot({path:`test-results/the-k-${filename}-desktop.png`,fullPage:true});
        await page.setViewportSize({width:390,height:844});await loadImages(page);await page.screenshot({path:`test-results/the-k-${filename}-mobile.png`,fullPage:true});
    }
    expect(errors).toEqual([]);expect((await page.goto('/not-a-page')).status()).toBe(404);
});
test('mobile navigation, complete service menu, gallery and keyboard dialogs',async({page})=>{
    await page.setViewportSize({width:390,height:844});await page.emulateMedia({reducedMotion:'reduce'});await page.goto('/');await page.getByRole('button',{name:'Toggle navigation'}).click();await expect(page.locator('#mobile-nav')).toHaveClass(/is-open/);await page.locator('#mobile-nav').getByRole('link',{name:'Services',exact:false}).click();
    await expect(page.locator('[data-menu-category]:visible')).toHaveCount(5);await expect(page.locator('[data-service-row]:visible')).toHaveCount(58);
    await expect(page.getByRole('searchbox')).toHaveCount(0);await expect(page.locator('[data-service-category],.ks-menu-toolbar')).toHaveCount(0);
    await page.goto('/gallery');await expect(page.locator('input[type=search]')).toHaveCount(0);await expect(page.locator('[data-gallery-filter],.ks-gallery-filters,.ks-image-source')).toHaveCount(0);const galleryCount=await page.locator('[data-gallery-category]').count();expect(galleryCount).toBeGreaterThan(0);await expect(page.locator('[data-gallery-category]:visible')).toHaveCount(galleryCount);await expect(page.locator('a[href*="pinterest"],a[href*="pin.it"]')).toHaveCount(0);await expect(page.locator('main')).not.toContainText('Pinterest');await page.locator('[data-lightbox]:visible').first().click();await expect(page.locator('#image-lightbox')).toBeVisible();await page.keyboard.press('Escape');await expect(page.locator('#image-lightbox')).toBeHidden();
    await page.locator('[data-open-dialog="appointment-policy"]').first().click();await expect(page.locator('#appointment-policy')).toBeVisible();await page.keyboard.press('Escape');await expect(page.locator('#appointment-policy')).toBeHidden();
});
test('contact page details, image and API CSRF protection',async({page,request})=>{
    await page.goto('/contact-us');
    await expect(page.locator('.ks-contact-details')).toBeVisible();
    await expect(page.locator('.ks-contact-photo')).toBeVisible();
    expect((await request.post('/inquiries/contact',{form:{full_name:'No token'}})).status()).toBe(419);
});



test('complete service menu stacks all categories in one column without filters',async({page})=>{
    await page.goto('/services');await expect(page.locator('[data-menu-category]:visible')).toHaveCount(5);
    await expect(page.locator('[data-service-row]:visible')).toHaveCount(58);await expect(page.locator('[data-service-row] a,[data-service-row] details,[data-menu-more],.ks-menu-toolbar')).toHaveCount(0);
    for(const width of [390,768,1280]){
        await page.setViewportSize({width,height:844});
        const boxes=await page.locator('[data-menu-category]').evaluateAll(parts=>parts.map(part=>{const box=part.getBoundingClientRect();return {x:box.x,y:box.y,width:box.width,height:box.height};}));
        for(let index=1;index<boxes.length;index++){expect(Math.abs(boxes[index].x-boxes[0].x)).toBeLessThan(1);expect(Math.abs(boxes[index].width-boxes[0].width)).toBeLessThan(1);expect(boxes[index].y).toBeGreaterThanOrEqual(boxes[index-1].y+boxes[index-1].height);}
    }
    const signature=page.locator('[data-service-row]').filter({hasText:'The K Signature Royal Pedicure'});await expect(signature).toContainText('$110');await expect(signature).toContainText('$125');await expect(signature.locator('.ks-menu-item-includes')).toBeVisible();
    await expect(page.getByRole('searchbox')).toHaveCount(0);
    await page.goto('/services?category=kids');await expect(page.locator('[data-menu-category]:visible')).toHaveCount(5);await expect(page.locator('[data-service-row]:visible')).toHaveCount(58);
    await page.goto('/');const pedicureLink=page.locator('.kl-collection-card[href="/services#menu-pedicures"]');await expect(pedicureLink).toHaveCount(1);await pedicureLink.click();await expect(page).toHaveURL(/\/services#menu-pedicures$/);await expect(page.locator('#menu-pedicures')).toBeInViewport();
});

test('floating social links and back to top work with smooth, native and reduced motion scrolling',async({page,browser,baseURL})=>{
    await page.addInitScript(()=>Object.defineProperty(navigator,'hardwareConcurrency',{get:()=>8}));
    for(const [width,motion] of [[1280,'no-preference'],[390,'no-preference'],[390,'reduce']]){
        await page.setViewportSize({width,height:800});await page.emulateMedia({reducedMotion:motion});await page.goto('/services');
        if(width===1280)await expect(page.locator('html')).toHaveClass(/lenis/);else await expect(page.locator('html')).not.toHaveClass(/lenis/);
        const actions=page.getByRole('navigation',{name:'Social links and page controls'});
        await expect(actions.getByRole('link',{name:/Facebook/})).toHaveAttribute('href',/facebook\.com/);await expect(actions.getByRole('link',{name:/Instagram/})).toHaveAttribute('href',/instagram\.com/);
        const boxes=await actions.locator('a').evaluateAll(links=>links.map(link=>({y:link.getBoundingClientRect().y,height:link.getBoundingClientRect().height})));
        expect(boxes).toHaveLength(3);expect(boxes[0].y+boxes[0].height).toBeLessThan(boxes[1].y);expect(boxes[1].y+boxes[1].height).toBeLessThan(boxes[2].y);expect(boxes.every(box=>width>730 ? box.height>=44 : box.height===32)).toBeTruthy();
        const railLeft=await actions.evaluate(el=>el.getBoundingClientRect().left);expect(await page.locator('[data-menu-category="nails"] .ks-menu-item-line>strong').evaluateAll((prices,left)=>prices.every(price=>price.getBoundingClientRect().right<left),railLeft)).toBeTruthy();
        await page.mouse.wheel(0,1200);await expect.poll(()=>page.evaluate(()=>scrollY)).toBeGreaterThan(500);await actions.getByRole('link',{name:'Back to top',exact:true}).click();await expect.poll(()=>page.evaluate(()=>scrollY)).toBeLessThan(2);
    }
    const context=await browser.newContext({javaScriptEnabled:false,reducedMotion:'reduce'});
    try{const fallback=await context.newPage();await fallback.goto(baseURL+'/services');await expect(fallback.locator('[data-service-row]:visible')).toHaveCount(58);await fallback.keyboard.press('End');await expect.poll(()=>fallback.evaluate(()=>scrollY)).toBeGreaterThan(500);await fallback.getByRole('link',{name:'Back to top',exact:true}).click();await expect.poll(()=>fallback.evaluate(()=>scrollY)).toBeLessThan(2);}finally{await context.close();}
});
test('smooth scrolling, inspiration slider and reduced motion preference',async({page})=>{
    await page.addInitScript(()=>Object.defineProperty(navigator,'hardwareConcurrency',{get:()=>8}));
    await page.setViewportSize({width:1280,height:800});await page.emulateMedia({reducedMotion:'no-preference'});await page.goto('/');await expect(page.locator('html')).toHaveClass(/lenis/);expect(await page.locator('.ks-reveal-pending').count()).toBeGreaterThan(0);
    await page.mouse.wheel(0,550);await expect.poll(()=>page.evaluate(()=>scrollY)).toBeGreaterThan(200);
    const slider=page.locator('[data-inspiration-slider]');await slider.scrollIntoViewIfNeeded();await page.locator('[data-slide="1"]').click();await expect.poll(()=>slider.evaluate(el=>el.scrollLeft)).toBeGreaterThan(50);
    await page.emulateMedia({reducedMotion:'reduce'});await expect(page.locator('html')).not.toHaveClass(/lenis/);await expect(page.locator('.ks-reveal-pending')).toHaveCount(0);
});


test('exclusive photo ownership and responsive editorial hero',async({page})=>{
    const used=new Map();await page.emulateMedia({reducedMotion:'reduce'});
    for(const path of ['/','/about-us','/services','/gallery','/contact-us']){
        await page.goto(path);
        for(const key of await page.locator('picture[data-salon-image]').evaluateAll(pics=>pics.map(pic=>pic.dataset.salonImage))){expect(used.has(key),`${key} reused on ${used.get(key)} and ${path}`).toBeFalsy();used.set(key,path);}
    }
    await page.setViewportSize({width:1440,height:1000});await page.goto('/');await expect(page.locator('main>section')).toHaveCount(8);
    const hero=page.locator('.kl-hero');const picture=hero.locator('.kl-hero-slide.is-active picture');expect((await picture.boundingBox()).width).toBeGreaterThan(720);await expect.poll(()=>picture.locator('img').evaluate(img=>img.currentSrc)).toContain('hero-marble-editorial.png');
    await page.setViewportSize({width:390,height:844});expect(Math.round((await picture.boundingBox()).width)).toBe(390);await expect.poll(()=>picture.locator('img').evaluate(img=>img.currentSrc)).toContain('hero-marble-editorial.png');
    await page.locator('.kl-faqs summary').first().click();await expect(page.locator('.kl-faqs details').first()).toHaveAttribute('open','');
});

test('mobile uses native scrolling and decorative lines draw once',async({page})=>{
    await page.setViewportSize({width:390,height:844});await page.emulateMedia({reducedMotion:'no-preference'});await page.goto('/');await expect(page.locator('html')).toHaveClass(/ks-motion/);await expect(page.locator('html')).not.toHaveClass(/lenis/);
    const line=page.locator('[data-draw-art]').first();await line.scrollIntoViewIfNeeded();await expect(line).toHaveClass(/is-drawn/);
    await page.emulateMedia({reducedMotion:'reduce'});await expect(page.locator('html')).not.toHaveClass(/ks-motion/);await expect(page.locator('.ks-reveal-pending')).toHaveCount(0);
});

test('hero fills its frame with a soft blend and transparent progress dots',async({page})=>{
    await page.emulateMedia({reducedMotion:'reduce'});
    for(const width of [360,390,768,1280,1440]){
        await page.setViewportSize({width,height:1000});await page.goto('/');
        const photo=await page.locator('.kl-hero-slides').boundingBox();const copy=await page.locator('.ks-hero-content').boundingBox();const frame=await page.locator('.kl-hero').boundingBox();expect(copy.y+copy.height).toBeLessThanOrEqual(frame.y+frame.height+1);
        if(width<=960)expect(copy.y).toBeGreaterThanOrEqual(photo.y+photo.height-1);else{expect(photo.x+photo.width-copy.x).toBeGreaterThan(80);expect(await page.locator('.kl-hero-slides').evaluate(el=>getComputedStyle(el,'::after').backgroundImage)).toContain('linear-gradient');}
        await expect(page.locator('.kl-hero-dots')).toHaveCSS('background-color','rgba(0, 0, 0, 0)');
        for(const index of [0,1,2]){
            await page.locator(`[data-hero-dot="${index}"]`).click();const image=page.locator('.kl-hero-slide.is-active img');await expect(image).toHaveCSS('object-fit','cover');const box=await image.boundingBox();expect(Math.abs(box.x-photo.x)).toBeLessThan(1);expect(Math.abs(box.width-photo.width)).toBeLessThan(1);expect(Math.abs(box.height-photo.height)).toBeLessThan(1);
        }
        expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1)).toBeTruthy();
    }
    const removed=await page.goto('/booking');expect(removed.status()).toBe(404);
});

test('home hero slideshow has accessible progress dots',async({page})=>{
    await page.setViewportSize({width:390,height:844});await page.emulateMedia({reducedMotion:'no-preference'});await page.goto('/');
    await expect(page.locator('[data-hero-slide]')).toHaveCount(3);await expect(page.locator('[data-hero-slide="0"]')).toHaveClass(/is-active/);
    await expect(page.locator('[data-hero-prev], [data-hero-next], [data-hero-pause]')).toHaveCount(0);
    await page.locator('[data-hero-dot="1"]').click();await expect(page.locator('[data-hero-slide="1"]')).toHaveClass(/is-active/);await expect(page.locator('[data-hero-dot="1"]')).toHaveAttribute('aria-pressed','true');
    await expect(page.getByRole('link',{name:'Follow THE K on Facebook'})).toHaveAttribute('href',/facebook\.com/);await expect(page.getByRole('link',{name:'Follow THE K on Instagram'})).toHaveAttribute('href',/instagram\.com/);
});
