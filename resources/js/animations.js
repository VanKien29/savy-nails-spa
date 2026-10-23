export function initAnimations() {
    if (matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;
    const frames = {
        'fade-up': [{opacity:0,transform:'translateY(24px)'},{opacity:1,transform:'translateY(0)'}],
        'fade-down': [{opacity:0,transform:'translateY(-24px)'},{opacity:1,transform:'translateY(0)'}],
        'slide-left': [{opacity:0,transform:'translateX(24px)'},{opacity:1,transform:'translateX(0)'}],
        'slide-right': [{opacity:0,transform:'translateX(-24px)'},{opacity:1,transform:'translateX(0)'}],
        scale: [{opacity:0,transform:'scale(.96)'},{opacity:1,transform:'scale(1)'}],
        'text-reveal': [{opacity:0,transform:'translateY(12px)'},{opacity:1,transform:'translateY(0)'}],
        'image-reveal': [{opacity:.2,transform:'scale(1.03)'},{opacity:1,transform:'scale(1)'}],
    };
    const observer = new IntersectionObserver(entries => entries.forEach(({target,isIntersecting}) => {
        if (!isIntersecting) return;
        const animation = frames[target.dataset.animate] || frames['fade-up'];
        // Never persist an invisible state: even a cancelled animation leaves visible HTML.
        if (typeof target.animate === 'function') target.animate(animation,{duration:600,delay:Math.min(Number(target.dataset.stagger)||0,500),easing:'cubic-bezier(.2,.7,.2,1)'});
        const count = Number(target.dataset.count);
        if (target.hasAttribute('data-count') && target.dataset.count !== '' && Number.isFinite(count)) {
            const start=performance.now();
            function tick(now) {const progress=Math.min(1,(now-start)/900);target.textContent=Math.round(count*progress).toLocaleString('vi-VN');if(progress<1)requestAnimationFrame(tick);}
            requestAnimationFrame(tick);
        }
        observer.unobserve(target);
    }),{threshold:.12});
    document.querySelectorAll('[data-animate],[data-count]').forEach(el=>observer.observe(el));
    const parallax=[...document.querySelectorAll('[data-parallax]')];
    const progress=document.querySelector('[data-scroll-progress]');
    if(parallax.length||progress){
        let pending=false;
        addEventListener('scroll',()=>{if(pending)return;pending=true;requestAnimationFrame(()=>{
            parallax.forEach(el=>{const rect=el.getBoundingClientRect();if(rect.bottom>0&&rect.top<innerHeight)el.style.transform=`translateY(${Math.max(-25,Math.min(25,(rect.top-innerHeight/2)*.04))}px)`;});
            if(progress)progress.style.transform=`scaleX(${scrollY/Math.max(1,document.documentElement.scrollHeight-innerHeight)})`;
            document.documentElement.classList.toggle('is-scrolled',scrollY>80);pending=false;
        });},{passive:true});
    }
}
