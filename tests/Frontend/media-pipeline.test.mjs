import test from 'node:test';
import assert from 'node:assert/strict';
import {promises as fs} from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {spawnSync} from 'node:child_process';
import sharp from 'sharp';

test('source pipeline preserves original/alpha, does not upscale and reports SVG skips',async()=>{
    const directory=await fs.mkdtemp(path.join(os.tmpdir(),'landing-media-'));
    const input=path.join(directory,'input'),output=path.join(directory,'output');await fs.mkdir(input);
    try{
        const pixels=Buffer.alloc(30*20*4);for(let i=0;i<pixels.length;i+=4){pixels[i]=160;pixels[i+1]=210;pixels[i+2]=50;pixels[i+3]=100;}
        await sharp(pixels,{raw:{width:30,height:20,channels:4}}).png().toFile(path.join(input,'sample.png'));
        const original=await fs.readFile(path.join(input,'sample.png'));
        await fs.writeFile(path.join(input,'logo.svg'),'<svg xmlns="http://www.w3.org/2000/svg"/>');
        const run=spawnSync(process.execPath,['scripts/optimize-media.mjs','--input',input,'--output',output],{encoding:'utf8'});
        assert.equal(run.status,0,run.stderr);
        const report=JSON.parse(await fs.readFile(path.join(output,'report.json'),'utf8'));
        const sample=report.find(row=>row.file==='sample.png');assert.equal(sample.variants.length,1);assert.equal(sample.variants[0].width,30);
        const result=await sharp(await fs.readFile(path.join(output,sample.variants[0].file))).metadata();assert.equal(result.format,'webp');assert.equal(result.hasAlpha,true);
        assert.deepEqual(await fs.readFile(path.join(input,'sample.png')),original);
        assert.ok(report.find(row=>row.file==='logo.svg').skipped);
    }finally{await fs.rm(directory,{recursive:true,force:true,maxRetries:10,retryDelay:100});}
});
