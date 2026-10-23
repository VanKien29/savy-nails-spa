import {test,expect} from '@playwright/test';
import {randomBytes,randomUUID} from 'node:crypto';
import {spawnSync} from 'node:child_process';
const email=`browser-${randomUUID()}@example.invalid`, password=randomBytes(24).toString('base64url');
function fixture(action){const result=spawnSync(process.env.STARTER_PHP||'php',['tests/Browser/fixture.php'],{input:JSON.stringify({action,email,password}),encoding:'utf8'});if(result.status!==0)throw new Error(`Browser fixture failed: ${result.stderr}`);}
test.beforeAll(()=>fixture('create'));
test.afterAll(()=>fixture('cleanup'));

test('desktop metadata, controls, assets and console',async({page})=>{
    const errors=[];page.on('pageerror',error=>errors.push(error.message));
    await page.goto('/');await expect(page.locator('h1')).toHaveCount(1);await expect(page).toHaveTitle(/Forma/);
    await expect(page.locator('link[rel=canonical]')).toHaveAttribute('href',/^http/);
    expect(await (await page.request.get('/robots.txt')).text()).toContain('Disallow: /');
    await page.locator('.accordion summary').first().click();await expect(page.locator('.accordion').first()).toHaveAttribute('open','');
    await page.evaluate(()=>scrollTo({top:0,behavior:'instant'}));
    await page.screenshot({path:'test-results/desktop.png',fullPage:true});
    expect(await page.locator('img[src]').evaluateAll(images=>images.every(image=>image.complete&&image.naturalWidth>0))).toBeTruthy();
    expect(errors).toEqual([]);
    for(const width of [360,768,1440]){await page.setViewportSize({width,height:900});expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();}
});
test('mobile menu, overflow and reduced motion',async({page})=>{
    await page.setViewportSize({width:390,height:844});await page.emulateMedia({reducedMotion:'reduce'});await page.goto('/');
    await page.getByRole('button',{name:'Mở menu'}).click();await expect(page.locator('#mobile-nav')).toHaveClass(/is-open/);
    await page.locator('#mobile-nav').getByRole('link',{name:'Dịch vụ',exact:true}).click();await expect(page.locator('#mobile-nav')).not.toHaveClass(/is-open/);
    expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();
    await page.screenshot({path:'test-results/mobile.png',fullPage:true});
});
test('lead submission and real CSRF rejection',async({page,request})=>{
    await page.goto('/');await page.getByRole('textbox',{name:'Họ và tên'}).fill('Browser test');
    await page.getByRole('textbox',{name:'Email',exact:true}).fill(email);await page.locator('[name=consent]').check();
    // Respect server anti-bot minimum age. Wait on the form response, not an arbitrary long sleep.
    await expect.poll(async()=>Date.now()-await page.evaluate(()=>performance.timeOrigin)).toBeGreaterThan(2100);
    await page.getByRole('button',{name:'Gửi lời nhắn'}).click();await expect(page.locator('[data-form-status]')).toContainText('đã được ghi nhận');
    const response=await request.post('/contact',{form:{full_name:'No csrf'}});expect(response.status()).toBe(419);
});
test('Filament login, resources, profile and logout',async({page})=>{
    await page.goto('/admin/login');await page.getByLabel(/Email|Địa chỉ email/i).fill(email);await page.getByLabel(/Mật khẩu|Password/).fill(password);
    await page.getByRole('button',{name:/^(Đăng nhập|Sign in)$/}).click();await expect(page).toHaveURL(/\/admin$/);
    for(const route of ['pages','leads','media','site-settings','profile']){const response=await page.goto(`/admin/${route}`);expect(response.status()).toBe(200);}
    await page.screenshot({path:'test-results/admin.png',fullPage:true});
    const token=await page.locator('meta[name="csrf-token"]').getAttribute('content');
    await page.request.post('/admin/logout',{form:{_token:token}});
    await page.goto('/admin');await expect(page).toHaveURL(/\/admin\/login$/);
});

test('local showcase dialog, keyboard tabs and lightbox',async({page})=>{
    const response=await page.goto('/starter/showcase');
    test.skip(response.status()===404,'Enable STARTER_SHOWCASE=true in local environment to exercise the optional showcase.');
    await page.getByRole('button',{name:'Mở hộp thoại'}).click();await expect(page.getByRole('dialog',{name:'Modal ví dụ'})).toBeVisible();
    await page.keyboard.press('Escape');await expect(page.getByRole('dialog',{name:'Modal ví dụ'})).not.toBeVisible();
    await page.getByRole('tab',{name:'Thiết kế'}).focus();await page.keyboard.press('ArrowRight');await expect(page.getByRole('tab',{name:'Hạ tầng'})).toHaveAttribute('aria-selected','true');
    await page.getByRole('button',{name:'Xem ảnh minh họa'}).click();await expect(page.getByRole('dialog',{name:'Xem ảnh',exact:true})).toBeVisible();
    await page.keyboard.press('Escape');
});
