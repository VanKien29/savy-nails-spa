import lighthouse from 'lighthouse';
import {launch} from 'chrome-launcher';
import {chromium} from '@playwright/test';
import {promises as fs} from 'node:fs';
const url=process.argv[2]||'http://127.0.0.1:8000';
const browser=await launch({chromePath:chromium.executablePath(),chromeFlags:['--headless=new']});
try{
    await fs.mkdir('test-results',{recursive:true});
    const result=await lighthouse(url,{port:browser.port,output:['html','json'],onlyCategories:['performance','accessibility','seo','best-practices'],logLevel:'error'});
    await fs.writeFile('test-results/lighthouse.html',result.report[0]);await fs.writeFile('test-results/lighthouse.json',result.report[1]);
    console.log(JSON.stringify({url,date:result.lhr.fetchTime,categories:Object.fromEntries(Object.entries(result.lhr.categories).map(([key,value])=>[key,value.score])),lcp:result.lhr.audits['largest-contentful-paint'].displayValue,cls:result.lhr.audits['cumulative-layout-shift'].displayValue,tbt:result.lhr.audits['total-blocking-time'].displayValue},null,2));
}finally{await browser.kill();}
