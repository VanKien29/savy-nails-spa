import {chromium} from '@playwright/test';
import assert from 'node:assert/strict';
import {promises as fs} from 'node:fs';
import path from 'node:path';

const origin=process.argv[2]||'http://127.0.0.1:8080';
const directory=path.resolve('dist/preview');
const files=await fs.readdir(directory,{recursive:true});
const routes=files.filter(file=>file.endsWith('index.html')).map(file=>'/'+file.replace(/index\.html$/,'').split(path.sep).join('/'));
const browser=await chromium.launch();
const report={origin,routes:[],checkedAt:new Date().toISOString()};
try{
    const page=await browser.newPage({viewport:{width:1440,height:1000}});
    await page.emulateMedia({reducedMotion:'reduce'});
    const errors=[];page.on('pageerror',error=>errors.push(error.message));
    for(const route of routes){
        const response=await page.goto(new URL(route,origin).href);assert.equal(response.status(),200);
        await page.locator('img[loading=lazy]').evaluateAll(images=>images.forEach(image=>image.loading='eager'));
        await page.locator('img[src]').evaluateAll(images=>Promise.all(images.map(image=>image.decode())));
        assert.match(await page.locator('meta[name=robots]').getAttribute('content'),/noindex/);
        assert.equal(await page.locator('h1').count(),1);
        assert.equal(await page.locator('input[name=_token],input[name=form_token],[data-tracking]').count(),0);
        assert.equal(await page.locator('[data-lead-form] button[type=submit][disabled], [data-salon-form] button[type=submit][disabled]').count(),0);
        assert.ok(await page.locator('[data-salon-form][data-static-preview]').count() <= 1);
        assert.equal(await page.locator('img[src]').evaluateAll(images=>images.every(image=>image.complete&&image.naturalWidth>0)),true);
        for(const asset of await page.locator('script[src],link[rel=stylesheet]').evaluateAll(elements=>elements.map(el=>el.src||el.href))){assert.equal((await page.request.get(asset)).status(),200);}
        if(await page.locator('[data-lightbox]').count()){
            await page.locator('[data-lightbox]').first().click();
            const source=await page.locator('#image-lightbox img').getAttribute('src');
            assert.equal((await page.request.get(new URL(source,origin).href)).status(),200);
            await page.keyboard.press('Escape');
        }
        report.routes.push({route,status:200,noindex:true,formsInteractive:true});
    }
    assert.match(await(await page.request.get(new URL('/robots.txt',origin).href)).text(),/Disallow: \//);
    for(const privatePath of ['/.env','/admin','/composer.json','/vendor/autoload.php'])assert.equal((await page.request.get(new URL(privatePath,origin).href)).status(),404);
    assert.deepEqual(errors,[]);
    await page.goto(origin);await page.screenshot({path:'test-results/static-preview.png'});
    await fs.writeFile('test-results/static-preview.json',JSON.stringify(report,null,2));
    console.log(JSON.stringify(report,null,2));
}finally{await browser.close();}

