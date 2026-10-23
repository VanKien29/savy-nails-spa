import sharp from 'sharp';
import { promises as fs } from 'node:fs';
import path from 'node:path';
const option=(name,fallback)=>{const index=process.argv.indexOf(name);return index===-1?fallback:process.argv[index+1];};
const input=path.resolve(option('--input','resources/media')), output=path.resolve(option('--output','public/assets/optimized'));
if(output===input||output.startsWith(input+path.sep))throw new Error('Output must be separate from input. Originals are never overwritten.');
await fs.mkdir(input,{recursive:true});await fs.mkdir(output,{recursive:true});
const report=[];
async function scan(directory){
    for(const entry of await fs.readdir(directory,{withFileTypes:true})){
        const source=path.join(directory,entry.name);
        if(entry.isSymbolicLink())continue;
        if(entry.isDirectory()){await scan(source);continue;}
        const relative=path.relative(input,source), size=(await fs.stat(source)).size;
        if(!/\.(jpe?g|png|webp|avif)$/i.test(entry.name)){report.push({file:relative,before:size,skipped:'SVG, GIF, logos and other formats preserved'});continue;}
        try{
            const image=sharp(source,{limitInputPixels:25000000,animated:true});const metadata=await image.metadata();
            if(metadata.pages>1){report.push({file:relative,before:size,skipped:'Animated image preserved'});continue;}
            const variants=[];
            for(const width of [...new Set([480,640,960,1280,1920].map(w=>Math.min(w,metadata.width)))]){
                for(const format of ['webp',...(process.argv.includes('--avif')&&sharp.format.avif?.output?.buffer?['avif']:[])]){
                    const filename=relative+`-${width}.${format}`;const destination=path.join(output,filename);await fs.mkdir(path.dirname(destination),{recursive:true});
                    const result=await sharp(source,{limitInputPixels:25000000}).rotate().resize({width,withoutEnlargement:true})[format]({quality:format==='avif'?55:82}).toFile(destination);
                    variants.push({file:path.relative(output,destination),width:result.width,height:result.height,bytes:result.size});
                }
            }
            report.push({file:relative,before:size,variants});
        }catch(error){report.push({file:relative,before:size,error:error.message});process.exitCode=1;}
    }
}
await scan(input);await fs.writeFile(path.join(output,'report.json'),JSON.stringify(report,null,2));
console.log(JSON.stringify(report,null,2));
