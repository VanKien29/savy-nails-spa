export function initTracking() {
    const config=JSON.parse(document.body.dataset.tracking||'{}');
    const banner=document.querySelector('[data-consent-banner]');
    let started=false;
    const read=()=>{try{return localStorage.getItem('starter.consent.v1');}catch{return null;}};
    const write=value=>{try{localStorage.setItem('starter.consent.v1',value);}catch{}};
    function script(src){const el=document.createElement('script');el.src=src;el.async=true;document.head.append(el);}
    function start(){
        if(started)return;started=true;
        window.dataLayer=window.dataLayer||[];
        if(/^GTM-[A-Z0-9]+$/.test(config.gtm_id)){window.dataLayer.push({'gtm.start':Date.now(),event:'gtm.js'});script(`https://www.googletagmanager.com/gtm.js?id=${config.gtm_id}`);}
        else if(/^G-[A-Z0-9]+$/.test(config.ga4_id)){
            window.gtag=function(){window.dataLayer.push(arguments);};window.gtag('js',new Date());window.gtag('config',config.ga4_id,{anonymize_ip:true});script(`https://www.googletagmanager.com/gtag/js?id=${config.ga4_id}`);
        }
        if(/^[0-9]{5,30}$/.test(config.meta_pixel_id)){
            const fbq=function(){fbq.callMethod?fbq.callMethod.apply(fbq,arguments):fbq.queue.push(arguments);};fbq.queue=[];fbq.loaded=true;fbq.version='2.0';window.fbq=fbq;script('https://connect.facebook.net/en_US/fbevents.js');fbq('init',config.meta_pixel_id);fbq('track','PageView');
        }
    }
    if(read()==='accepted')start();else if(read()!=='rejected'&&banner)banner.hidden=false;
    document.querySelectorAll('[data-consent]').forEach(button=>button.addEventListener('click',()=>{
        const consent=button.dataset.consent;write(consent);if(banner)banner.hidden=true;
        if(consent==='accepted')start();else if(started)location.reload();
    }));
    document.querySelectorAll('[data-reset-consent]').forEach(button=>button.addEventListener('click',()=>{if(banner)banner.hidden=false;}));
    document.querySelectorAll('[data-contact]').forEach(link=>link.addEventListener('click',()=>{if(read()==='accepted')window.dataLayer?.push({event:'contact_click',channel:link.dataset.contact});}));
}
