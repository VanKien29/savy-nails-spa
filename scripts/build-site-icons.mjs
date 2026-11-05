import sharp from 'sharp';
import {readFile, writeFile, mkdir} from 'node:fs/promises';

// The monogram is outlined SVG, so neither it nor its raster fallbacks need fonts.
const brand = 'savy';
const version = 'savy-20261104';
const source = await readFile(`public/assets/${brand}/favicon.svg`);
await mkdir(`resources/media/${brand}`, {recursive:true});
await sharp(source).resize(512,512).png().toFile(`resources/media/${brand}/site-icon.png`);
for (const size of [16,32,180,192,512]) {
    await sharp(source).resize(size,size).png({palette:true}).toFile(`public/assets/${brand}/favicon-${size}.png`);
}
const sizes = [16,32,48];
const images = await Promise.all(sizes.map(size => sharp(source).resize(size,size).png().toBuffer()));
const header = Buffer.alloc(6 + 16 * images.length);
header.writeUInt16LE(1,2);
header.writeUInt16LE(images.length,4);
let offset = header.length;
images.forEach((image,index) => {
    const entry = 6 + index * 16;
    header[entry] = sizes[index];
    header[entry+1] = sizes[index];
    header.writeUInt16LE(1,entry+4);
    header.writeUInt16LE(32,entry+6);
    header.writeUInt32LE(image.length,entry+8);
    header.writeUInt32LE(offset,entry+12);
    offset += image.length;
});
await writeFile('public/favicon.ico', Buffer.concat([header,...images]));
await writeFile('public/favicon.svg', source);
await writeFile('public/site.webmanifest', JSON.stringify({
    name:'Savy Nails & Spa',short_name:'SAVY',start_url:'/',display:'browser',
    theme_color:'#a27a40',background_color:'#faf7f0',
    icons:[192,512].map(size=>({src:`/assets/${brand}/favicon-${size}.png?v=${version}`,sizes:`${size}x${size}`,type:'image/png'})),
},null,2)+'\n');
console.log('Built outlined SVG, multi-size ICO, PNG favicons and web manifest.');
