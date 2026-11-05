import {test,expect} from '@playwright/test';
import {randomUUID} from 'node:crypto';
import {spawnSync} from 'node:child_process';

test('admin exposes only current modules and keeps the useful editors',async({page})=>{
    test.setTimeout(120000);
    const email=`browser-${randomUUID()}@example.invalid`;
    const targetEmail=`browser-${randomUUID()}@example.invalid`;
    const password=`K${randomUUID()}a9!`;
    function fixture(action,accountEmail=email){
        const result=spawnSync(process.env.STARTER_PHP||'php',['tests/Browser/fixture.php'],{
            input:JSON.stringify({action,email:accountEmail,password}),encoding:'utf8',
        });
        if(result.status!==0)throw new Error(result.stderr||'Admin fixture failed');
    }
    fixture('create');
    fixture('create',targetEmail);
    try{
        await page.setViewportSize({width:1440,height:1000});
        await page.goto('/admin/login');
        await page.locator('input[type=email]').fill(email);
        await page.locator('input[type=password]').fill(password);
        await page.getByRole('button',{name:/Đăng nhập|Sign in/i}).click();
        await expect(page).toHaveURL(/\/admin$/, {timeout:20000});
        await expect(page.locator('.fi-sidebar')).not.toContainText('Khách liên hệ');
        await expect(page.locator('.fi-sidebar').getByRole('link',{name:'Cấu hình website',exact:true})).toHaveCount(0);
        await page.getByRole('button',{name:'Menu người dùng'}).click();
        await expect(page.getByText('Hồ sơ',{exact:true})).toHaveCount(0);
        await expect(page.getByText('Đăng xuất',{exact:true}).last()).toBeVisible();
        await page.screenshot({path:'test-results/admin-account-menu.png',animations:'disabled'});
        await page.keyboard.press('Escape');
        for(const route of ['salon-settings','leads','media','navigation-items','redirects']){
            expect((await page.request.get(`/admin/${route}`)).status()).toBe(404);
        }
        for(const route of ['site-settings','header-menu','footer-settings','seo-settings']){
            await page.goto(`/admin/${route}`);
            await expect(page.locator('form[wire\\:submit=save]')).toBeVisible();
            if(route==='site-settings'){
                for(const label of ['Slogan','Liên hệ nổi (kéo để sắp xếp)','Email liên hệ','Hreflang']){
                    await expect(page.getByText(label,{exact:true})).toHaveCount(0);
                }
            }
            await page.evaluate(()=>document.fonts.ready);
            await page.screenshot({path:`test-results/admin-${route}.png`,fullPage:true,animations:'disabled'});
        }
        await page.goto('/admin/website-content?section=gallery');
        const gallery=page.locator('.sample-image-field.is-compact');
        const images=gallery.locator('.sample-selected-images>figure');
        await expect(images.first()).toBeVisible();
        await expect(gallery.locator('figcaption')).toHaveCount(0);
        for(const label of ['Chú thích khi mở ảnh lớn','Mô tả ảnh','Nút phóng to','Ghi chú dưới thư viện ảnh']){
            await expect(page.getByText(label,{exact:true})).toHaveCount(0);
        }
        const firstImage=await images.first().locator('img').getAttribute('alt');
        await images.first().getByRole('button',{name:'Đưa ảnh ra sau'}).click();
        await expect(images.nth(1).locator('img')).toHaveAttribute('alt',firstImage);
        await images.nth(1).getByRole('button',{name:'Đưa ảnh về trước'}).click();
        await expect(images.first().locator('img')).toHaveAttribute('alt',firstImage);
        await gallery.getByRole('button',{name:'Chọn ảnh từ Thư viện Media'}).click();
        await expect(gallery.getByRole('dialog',{name:'Chọn ảnh',exact:true})).toBeVisible();
        await gallery.getByRole('button',{name:'Xong',exact:true}).click();
        await page.screenshot({path:'test-results/admin-gallery-compact.png',fullPage:true,animations:'disabled'});
        await page.goto('/admin/salon-services');
        await expect(page).toHaveURL(/\/admin\/service-menu/);
        await page.getByRole('button',{name:'Thêm dịch vụ',exact:true}).first().click();
        await expect(page.getByRole('textbox',{name:/Tên dịch vụ/})).toBeVisible();
        await expect(page.getByText('Mã đường dẫn',{exact:true})).toHaveCount(0);
        await page.screenshot({path:'test-results/admin-service-form.png',fullPage:true,animations:'disabled'});
        await page.goto('/admin/users');
        await page.getByRole('searchbox').fill(targetEmail);
        const targetRow=page.getByRole('row').filter({hasText:targetEmail});
        await expect(targetRow).toBeVisible();
        await expect(page.getByRole('table').getByRole('row')).toHaveCount(2);
        await targetRow.getByRole('button',{name:'Xóa',exact:true}).click();
        const confirmation=page.getByRole('alertdialog');
        await expect(confirmation).toContainText('Tài khoản sẽ bị xóa vĩnh viễn');
        await confirmation.getByRole('button',{name:'Xóa tài khoản',exact:true}).click();
        await expect(targetRow).toHaveCount(0);
    }finally{fixture('cleanup',targetEmail);fixture('cleanup');}
});
