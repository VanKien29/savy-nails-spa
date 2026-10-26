<?php

namespace Database\Seeders;

class SalonCatalogData
{
    public static function categories(): array
    {
        $categories = array (
  0 => 
  array (
    'slug' => 'pedicures',
    'name' => 'Spa Pedicures',
    'description' => 'Slow down with a beautifully considered ritual, from a fresh everyday pedicure to our signature royal experience.',
    'image' => 'rose-champagne',
  ),
  1 => 
  array (
    'slug' => 'nails',
    'name' => 'Nail Services',
    'description' => 'From a clean, natural finish to a statement set, find your own kind of polished.',
    'image' => 'hero-manicure',
  ),
  2 => 
  array (
    'slug' => 'brows-lashes',
    'name' => 'Brows & Lashes',
    'description' => 'Thoughtful definition for your eyes, brows and everyday beauty.',
    'image' => 'brows-lashes',
  ),
  3 => 
  array (
    'slug' => 'waxing',
    'name' => 'Waxing',
    'description' => 'The finishing details, with a smooth and carefully considered touch.',
    'image' => 'kids-waxing',
  ),
  4 => 
  array (
    'slug' => 'kids',
    'name' => 'Kid Services',
    'description' => 'A little pampering for our youngest guests. Services for children under 12.',
    'image' => 'kids-waxing',
  ),
);
        return array_map(function (array $row) use ($categories): array {
            $existing = collect($categories)->firstWhere('slug', $row['slug']);
            return array_replace($existing, $row);
        }, ClientMenuData::document()['categories']);
    }

    public static function services(): array
    {
        return ClientMenuData::services();
    }

    public static function menuImages(): array
    {
        return array (
  0 => 
  array (
    'key' => 'hero-manicure',
    'source' => 'hero-manicure.png',
    'alt' => 'French almond manicure with delicate gold jewelry on ivory silk',
    'caption' => 'French, with a golden touch',
    'category' => 'nails',
    'is_inspiration' => true,
    'show_in_gallery' => true,
  ),
  1 => 
  array (
    'key' => 'blush-manicure',
    'source' => 'blush-manicure.png',
    'alt' => 'Blush pink glazed almond manicure on soft ivory satin',
    'caption' => 'Soft blush, beautifully polished',
    'category' => 'nails',
    'is_inspiration' => true,
    'show_in_gallery' => true,
  ),
  2 => 
  array (
    'key' => 'burgundy-manicure',
    'source' => 'burgundy-manicure.png',
    'alt' => 'Deep burgundy almond nails with warm gold jewelry',
    'caption' => 'A little bold. Always elegant.',
    'category' => 'nails',
    'is_inspiration' => true,
    'show_in_gallery' => true,
  ),
  3 => 
  array (
    'key' => 'rose-champagne',
    'source' => 'rose-champagne.jpg',
    'alt' => 'Rosé Champagne Bliss Pedicure treatment menu and rose-inspired spa setting',
    'caption' => 'Rosé Champagne Bliss',
    'category' => 'rituals',
    'is_inspiration' => false,
    'show_in_gallery' => true,
  ),
  4 => 
  array (
    'key' => 'signature-royal',
    'source' => 'signature-royal.jpg',
    'alt' => 'The K Signature Royal Pedicure menu with botanical tea and spa preparations',
    'caption' => 'The K Signature Royal Ritual',
    'category' => 'rituals',
    'is_inspiration' => false,
    'show_in_gallery' => true,
  ),
  5 => 
  array (
    'key' => 'golden-queen',
    'source' => 'golden-queen.jpg',
    'alt' => 'Golden 99K Fairy Queen Pedicure treatment menu',
    'caption' => 'Golden 99K Fairy Queen',
    'category' => 'rituals',
    'is_inspiration' => false,
    'show_in_gallery' => true,
  ),
  6 => 
  array (
    'key' => 'lavender',
    'source' => 'lavender.jpg',
    'alt' => 'Lavender Dream Pedicure menu with lavender spa ingredients',
    'caption' => 'Lavender Dream',
    'category' => 'rituals',
    'is_inspiration' => false,
    'show_in_gallery' => true,
  ),
  7 => 
  array (
    'key' => 'matcha',
    'source' => 'matcha.jpg',
    'alt' => 'Matcha Glow Pedicure menu with green tea spa preparations',
    'caption' => 'The Matcha Glow Ritual',
    'category' => 'rituals',
    'is_inspiration' => false,
    'show_in_gallery' => true,
  ),
  8 => 
  array (
    'key' => 'milk-honey',
    'source' => 'milk-honey.jpg',
    'alt' => 'Milk and Honey Pedicure menu with orange and honey spa ingredients',
    'caption' => 'Milk & Honey',
    'category' => 'rituals',
    'is_inspiration' => false,
    'show_in_gallery' => false,
  ),
  9 => 
  array (
    'key' => 'pearl',
    'source' => 'pearl.jpg',
    'alt' => 'Pearl Essence Pedicure menu with pearl-inspired spa preparations',
    'caption' => 'Pearl Essence',
    'category' => 'rituals',
    'is_inspiration' => false,
    'show_in_gallery' => false,
  ),
  10 => 
  array (
    'key' => 'aloe-vera',
    'source' => 'aloe-vera.jpg',
    'alt' => 'Aloe Vera Pedicure menu with fresh aloe spa preparations',
    'caption' => 'Aloe Vera',
    'category' => 'rituals',
    'is_inspiration' => false,
    'show_in_gallery' => false,
  ),
  11 => 
  array (
    'key' => 'espresso',
    'source' => 'espresso.jpg',
    'alt' => 'Espresso Spa Pedicure menu with coffee-inspired spa preparations',
    'caption' => 'Espresso Spa',
    'category' => 'rituals',
    'is_inspiration' => false,
    'show_in_gallery' => false,
  ),
  12 => 
  array (
    'key' => 'tropical-orange',
    'source' => 'tropical-orange.jpg',
    'alt' => 'Tropical and Fresh Orange Pedicure service details and pricing',
    'caption' => 'Tropical & Fresh Orange',
    'category' => 'menus',
    'is_inspiration' => false,
    'show_in_gallery' => false,
  ),
  13 => 
  array (
    'key' => 'brows-lashes',
    'source' => 'brows-lashes.jpg',
    'alt' => 'The K brows, permanent makeup and lash services menu',
    'caption' => 'Brows & Lashes',
    'category' => 'beauty',
    'is_inspiration' => false,
    'show_in_gallery' => true,
  ),
  14 => 
  array (
    'key' => 'kids-waxing',
    'source' => 'kids-waxing.jpg',
    'alt' => 'The K children’s services, waxing and basic pedicure menu',
    'caption' => 'Little Luxuries & Waxing',
    'category' => 'menus',
    'is_inspiration' => false,
    'show_in_gallery' => false,
  ),
  15 => 
  array (
    'key' => 'nails-menu',
    'source' => 'nails-menu.jpg',
    'alt' => 'The K full set, fill-in, dip powder and manicure menu',
    'caption' => 'The Nail Menu',
    'category' => 'menus',
    'is_inspiration' => false,
    'show_in_gallery' => false,
  ),
  16 => 
  array (
    'key' => 'brand-logo',
    'source' => 'brand-logo.jpg',
    'alt' => 'The K Nail Bar gold monogram and brand artwork',
    'caption' => 'The K Nail Bar',
    'category' => 'brand',
    'is_inspiration' => false,
    'show_in_gallery' => false,
  ),
);
    }

    public static function luxuryImages(): array
    {
        return array (
  0 => 
  array (
    'key' => 'luxury-hero',
    'source' => 'luxury-hero.png',
    'placement' => 'home.hero',
    'caption' => 'The K after dark',
    'alt' => 'The K after dark — luxury editorial nail and spa inspiration',
    'prompt' => 'Create an ultra high quality photorealistic luxury nail editorial website hero photograph, very wide horizontal 16:9 composition. A naturally elegant feminine hand, anatomically accurate five fingers, with clearly LONG sculpted tapered coffin nails in translucent milky nude pink, immaculate fine ivory French tips and extremely subtle champagne gold accents. A single fine sculptural gold ring. Hand resting gracefully on dark charcoal satin and black honed marble, composed in the RIGHT half of the image. Dramatic soft lateral studio light, warm champagne highlights on skin and nails, rich true charcoal shadows, crisp clean nail edges and genuine detailed skin texture. The LEFT 45 percent remains dark, simple but tactile charcoal fabric with soft folds, ample negative space for white website headline. Scene fills entire image edge to edge. Expensive restrained beauty advertising photography, cohesive palette charcoal black, ivory, nude and champagne gold, no flowers, no bottles, no oversaturated orange. No typography, no logo, no watermark, no borders. Sharp professional photograph suitable for full width desktop banner.',
  ),
  1 => 
  array (
    'key' => 'luxury-hero-mobile',
    'source' => 'luxury-hero-mobile-bottom.png',
    'placement' => 'home.hero_mobile',
    'caption' => 'A little luxury, wherever you are',
    'alt' => 'A little luxury, wherever you are — luxury editorial nail and spa inspiration',
    'prompt' => 'Edit this luxury nail photograph for a mobile website hero with TEXT AT THE BOTTOM. Preserve the exact high-end charcoal satin / black marble / champagne-gold lighting and the elegant natural feminine hand with LONG nude-pink coffin nails, ivory French tips and fine gold accents. Recompose the hand into the UPPER RIGHT half of the vertical 3:4 portrait, with the fingernail tips clearly visible around the upper-middle of the frame. Keep the LOWER 45 PERCENT as quiet very dark charcoal satin negative space, with no hand or bright marble there, so a white headline and booking buttons can be placed at the bottom. Show the whole beautiful set without clipping fingers, anatomy natural. Crisp professional beauty photograph. No lettering, no added words, no logo, no watermark. Full bleed image.',
    'previous_source' => 'luxury-hero-mobile.png',
    'previous_prompt' => 'Photorealistic high-end nail editorial portrait for a MOBILE website hero, vertical 3:4. An elegant feminine hand with naturally correct anatomy, long nude pink tapered coffin nails with fine ivory French tips and a trace of champagne gold, one subtle gold ring. Hand rests diagonally in the LOWER RIGHT 60 percent of frame on dark charcoal satin and black marble. Upper left and upper 45 percent are quiet deep charcoal fabric negative space for a white headline. Dramatic soft warm side light, sharp nail edges and realistic detailed skin. Luxury restrained charcoal, ivory and nude palette. Full bleed photograph with no text, no watermark, no logo, no flower props, no border.',
  ),
  2 => 
  array (
    'key' => 'luxury-story',
    'source' => 'luxury-story.png',
    'placement' => 'home.story',
    'caption' => 'A considered kind of beauty',
    'alt' => 'A considered kind of beauty — luxury editorial nail and spa inspiration',
    'prompt' => 'Photorealistic luxury nail editorial campaign photograph, portrait 3:4 aspect ratio, anatomically natural elegant adult feminine hand, correctly formed fingers and realistic skin texture, clearly LONG nails with immaculate precise edges and glossy realistic highlights. High-end restrained beauty advertising, muted ivory, pale nude, champagne gold and charcoal palette. Soft directional studio lighting. Crisp high resolution detail, no blur on nails, no watermark, no text, no collage, no logo. A graceful single hand with long almond nails in sheer milky nude, a tiny subtle gold crescent at each nail base, one slender gold ring, resting lightly on a sculptural ivory silk fold over pale warm travertine. Composition airy and architectural, skin illuminated in soft daylight, macro but show entire fingers, no flowers or other props. Warm creamy light background with soft graphite shadows. Quiet refined beauty.',
  ),
  3 => 
  array (
    'key' => 'luxury-ritual',
    'source' => 'luxury-ritual.png',
    'placement' => 'archived.home.ritual',
    'caption' => 'The art of slowing down',
    'alt' => 'The art of slowing down — luxury editorial nail and spa inspiration',
    'prompt' => 'Create a photorealistic premium spa beauty editorial photograph, landscape 4:3 composition. A sculptural brushed champagne-gold foot spa bowl with clear warm water, several ivory orchid petals and one ivory orchid branch, elegantly arranged on dark charcoal honed marble. Folded ivory cotton towels, a small unlabeled amber oil bottle and a single elegant candle set back softly out of focus. No human feet or hands. Editorial still life with restrained expensive styling and spacious composition, soft directional warm studio light, muted cream and champagne highlights against velvety charcoal-black shadows. Extremely crisp photographic detail on water, towel loops, flower and metal. Contemporary high-end beauty brand, no clutter, no pink/red roses, no orange color cast, no text, no logos, no watermark. This is an editorial concept still life, not a salon interior.',
  ),
  4 => 
  array (
    'key' => 'luxury-look-french',
    'source' => 'luxury-look-french.png',
    'placement' => 'home.lookbook.1',
    'caption' => 'The French Edit',
    'alt' => 'The French Edit — luxury editorial nail and spa inspiration',
    'prompt' => 'Photorealistic luxury nail editorial campaign photograph, portrait 3:4 aspect ratio, anatomically natural elegant adult feminine hand, correctly formed fingers and realistic skin texture, clearly LONG nails with immaculate precise edges and glossy realistic highlights. High-end restrained beauty advertising, muted ivory, pale nude, champagne gold and charcoal palette. Soft directional studio lighting. Crisp high resolution detail, no blur on nails, no watermark, no text, no collage, no logo. A single hand with LONG tapered square coffin nails, translucent nude pink base and perfectly fine clean ivory French tips. Fingers gently curved naturally, photographed diagonally across pale limestone, a sculptural small champagne-gold ring, a little folded cream silk in bottom corner. Bright ivory background, soft warm morning light. Focus on graceful length and precision. No flower props.',
  ),
  5 => 
  array (
    'key' => 'luxury-look-chrome',
    'source' => 'luxury-look-chrome.png',
    'placement' => 'home.lookbook.2',
    'caption' => 'Champagne Chrome',
    'alt' => 'Champagne Chrome — luxury editorial nail and spa inspiration',
    'prompt' => 'Photorealistic luxury nail editorial campaign photograph, portrait 3:4 aspect ratio, anatomically natural elegant adult feminine hand, correctly formed fingers and realistic skin texture, clearly LONG nails with immaculate precise edges and glossy realistic highlights. High-end restrained beauty advertising, muted ivory, pale nude, champagne gold and charcoal palette. Soft directional studio lighting. Crisp high resolution detail, no blur on nails, no watermark, no text, no collage, no logo. A poised single hand with LONG almond nails finished in a luminous subtle champagne pearl chrome, with a thin gold ring. The hand gently touches an off-white sculptural ceramic orb, cream plaster backdrop, soft deep side shadows. Refined editorial closeup with accurate anatomy, neutral ivory and pale metallic champagne only, no vivid glitter or gemstones.',
  ),
  6 => 
  array (
    'key' => 'luxury-look-gold',
    'source' => 'luxury-look-gold.png',
    'placement' => 'home.lookbook.3',
    'caption' => 'Gold, in the details',
    'alt' => 'Gold, in the details — luxury editorial nail and spa inspiration',
    'prompt' => 'Photorealistic luxury nail editorial campaign photograph, portrait 3:4 aspect ratio, anatomically natural elegant adult feminine hand, correctly formed fingers and realistic skin texture, clearly LONG nails with immaculate precise edges and glossy realistic highlights. High-end restrained beauty advertising, muted ivory, pale nude, champagne gold and charcoal palette. Soft directional studio lighting. Crisp high resolution detail, no blur on nails, no watermark, no text, no collage, no logo. A relaxed naturally posed single hand with LONG sculpted coffin nails in sheer nude and delicate ultra-thin metallic gold French outline tips. A slim organically shaped gold ring. Hand resting on folded matte charcoal fabric with an ivory silk ribbon entering one corner, dramatic soft champagne highlights. Nails clearly visible, restrained jewelry editorial feel, no chunky rhinestones.',
  ),
  7 => 
  array (
    'key' => 'luxury-look-milky',
    'source' => 'luxury-look-milky.png',
    'placement' => 'home.lookbook.4',
    'caption' => 'Milky Minimalism',
    'alt' => 'Milky Minimalism — luxury editorial nail and spa inspiration',
    'prompt' => 'Photorealistic luxury nail editorial campaign photograph, portrait 3:4 aspect ratio, anatomically natural elegant adult feminine hand, correctly formed fingers and realistic skin texture, clearly LONG nails with immaculate precise edges and glossy realistic highlights. High-end restrained beauty advertising, muted ivory, pale nude, champagne gold and charcoal palette. Soft directional studio lighting. Crisp high resolution detail, no blur on nails, no watermark, no text, no collage, no logo. A single elegant hand with LONG narrow almond nails coated in glossy milky ivory, with one understated thin gold band. Hand softly resting on beige travertine next to an ivory linen fold, a quiet black shadow in background. Contemporary minimal beauty portrait, creamy light tones, calm delicate sculptural pose. Nail shape and skin in tack-sharp focus.',
  ),
  8 => 
  array (
    'key' => 'luxury-gallery-sculpture',
    'source' => 'luxury-gallery-sculpture.png',
    'placement' => 'archived.home.ritual_support',
    'caption' => 'Sculptural Nude',
    'alt' => 'Sculptural Nude — luxury editorial nail and spa inspiration',
    'prompt' => 'Photorealistic luxury beauty editorial, portrait 3:4. Elegant feminine hand and wrist with long almond-shaped nails in glossy sheer champagne nude, hand softly arched with palm down and fingers naturally relaxed, emerging from an elegant BLACK tailored silk sleeve. One slim gold ring. The hand rests on a light ivory sculptural plaster curve, smooth cream background and deep architectural side shadow. Professional magazine beauty photography, very crisp nail and skin texture, realistic anatomy, quiet muted champagne gold, ivory and charcoal color palette. No words, logo or watermark. Clearly long refined nails.',
  ),
  9 => 
  array (
    'key' => 'luxury-gallery-ribbon',
    'source' => 'luxury-gallery-ribbon.png',
    'placement' => 'gallery.2',
    'caption' => 'The Silk French',
    'alt' => 'The Silk French — luxury editorial nail and spa inspiration',
    'prompt' => 'Photorealistic luxury beauty editorial portrait 3:4. A naturally relaxed elegant feminine hand with long precise square French nails, nude pink base and thin ivory tips with tiny gold corner detail, fingers gently holding the edge of an ivory satin ribbon draped over charcoal fabric. View from slightly above, a different graceful wrist angle, one slender sculptural gold band. Rich dark background and warm soft light on nails. Anatomically accurate fingers and realistic skin, tack-sharp glossy nail detail. Muted ivory, nude, champagne gold and black palette. No text, no watermark, no collage, no logos.',
  ),
  10 => 
  array (
    'key' => 'luxury-gallery-pearl',
    'source' => 'luxury-gallery-pearl.png',
    'placement' => 'gallery.3',
    'caption' => 'A Touch of Pearl',
    'alt' => 'A Touch of Pearl — luxury editorial nail and spa inspiration',
    'prompt' => 'Photorealistic fine beauty editorial macro portrait 3:4. A graceful feminine hand with LONG almond nails, soft milky pearl finish and one extremely small gold crescent accent on the ring fingernail. Hand posed sideways diagonally over pale travertine, fingers naturally curved, beside a delicate strand of small real pearls and a soft cream linen fold. Quiet architectural composition with negative space and sharp focused nail detail, realistic human anatomy and natural skin. Cream, beige, champagne gold palette with a little charcoal shadow. Understated luxury, no text, watermark or logo.',
  ),
  11 => 
  array (
    'key' => 'luxury-ritual-nude',
    'source' => 'luxury-ritual-nude.png',
    'placement' => 'home.ritual.1',
    'caption' => 'Nude with a golden finish',
    'alt' => 'Long blush nude coffin nails with fine gold French tips resting on ivory silk and limestone.',
    'prompt' => 'New luxury nail editorial photograph for a white and champagne gold nail salon website. Landscape 4:3 full bleed composition. A naturally posed adult feminine hand with anatomically correct fingers, beautiful clearly LONG tapered square coffin nails in glossy sheer blush nude with extremely delicate champagne gold outline French tips, one slender gold ring. Hand rests diagonally with fingers relaxed and slightly apart on pristine ivory silk folds over warm pale limestone. All long nails clearly visible and safely inside frame with plenty of room for responsive cropping. Bright soft daylight, gentle elegant shadows, refined creamy ivory, pale nude and subtle gold palette, tack sharp skin texture and immaculate realistic glossy nail edges. Professional luxury beauty advertising photograph. No black clothes, no dark backgrounds, no spa bowls, no flowers, no tools, no text, no logos, no collage, no watermark.',
  ),
  12 => 
  array (
    'key' => 'luxury-ritual-pearl',
    'source' => 'luxury-ritual-pearl.png',
    'placement' => 'home.ritual.2',
    'caption' => 'The soft pearl detail',
    'alt' => 'Long pearlescent almond nails with delicate gold crescents on an ivory ceramic pedestal.',
    'prompt' => 'New luxury nail editorial photograph for the second image on a white and gold nail salon website. Landscape 4:3 full bleed composition. A single elegant adult feminine hand seen from a graceful side angle, fingers gently curved over the rounded edge of an off-white sculptural ceramic pedestal. Natural anatomically accurate fingers with clearly LONG slender almond nails in soft milky pearl white chrome and tiny fine warm gold crescent accents near cuticles, one small minimal gold ring. Show the complete nail lengths safely inside the frame, including fingertips, generous space for responsive cropping. Matte warm ivory plaster background and a small cream linen fold at the lower edge, luminous soft window lighting, gentle champagne highlights, realistic detailed skin and pristine sharp nails. High-end magazine beauty photography with quiet ivory, nude and champagne gold colors. No black sleeves, no black or dark background, no flowers, no bottles, no spa products, no text, no logos, no collage, no watermark.',
  ),
  13 => 
  array (
    'key' => 'contact-welcome',
    'source' => 'contact-welcome.jpg',
    'placement' => 'contact.visual',
    'caption' => 'We\'d love to hear from you',
    'alt' => 'Luxury salon welcome vignette — ivory peonies, linen towels, candle and gold accents on travertine',
    'prompt' => 'Photorealistic luxury nail salon welcome still life photograph, portrait 3:4 composition. A beautifully arranged console table vignette in a high-end nail salon reception area: a small bouquet of ivory peonies and soft blush roses in a matte champagne-gold vase, beside elegant stacked ivory linen hand towels tied with a thin gold satin ribbon, a small scented candle in an amber glass jar with warm flickering light, and a delicate gold-framed card holder. Warm creamy travertine surface and soft ivory plaster wall background. Soft natural window light from the left creating gentle warm shadows. Muted luxury palette: ivory, cream, warm beige, champagne gold, soft blush. Extremely crisp photographic detail on textures. Premium spa hospitality aesthetic, restrained and elegant. No human hands or people. No text, no logo, no watermark. Professional interior styling photography.',
  ),
);
    }

    public static function pinterestImages(): array
    {
        return array (
  0 => 
  array (
    'key' => 'pinterest-gold-coffin',
    'source' => 'pinterest-gold-coffin.jpg',
    'alt' => 'Long nude coffin nails with metallic gold French tips',
    'caption' => 'Golden French, made to stand out',
    'source_url' => 'https://www.pinterest.com/pin/396809417181460689/',
    'source_credit' => 'Pinterest · @_nails.byamyx',
  ),
  1 => 
  array (
    'key' => 'pinterest-pearl-chrome',
    'source' => 'pinterest-pearl-chrome.jpg',
    'alt' => 'Long almond nails with pearl accents and iridescent chrome French tips',
    'caption' => 'Pearl details. Chrome finish.',
    'source_url' => 'https://mx.pinterest.com/pin/211174975975458/',
    'source_credit' => 'Pinterest inspiration',
  ),
  2 => 
  array (
    'key' => 'pinterest-burgundy-french',
    'source' => 'pinterest-burgundy-french.jpg',
    'alt' => 'Long square nails with deep burgundy French tips',
    'caption' => 'A little length. A bold French.',
    'source_url' => 'https://ca.pinterest.com/pin/2885187255637821/',
    'source_credit' => 'Pinterest · @liv.jvd',
  ),
  3 => 
  array (
    'key' => 'pinterest-long-french',
    'source' => 'pinterest-long-french.png',
    'alt' => 'Extra long pink and white square French nails with gold rings',
    'caption' => 'The long French edit',
    'source_url' => 'https://www.pinterest.com/pin/601019512758741336/',
    'source_credit' => 'Pinterest · @clawedbypaola',
  ),
);
    }
}
