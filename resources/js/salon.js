import './site';
const navToggle = document.querySelector('[data-salon-nav-toggle]');
const mobileNav = document.getElementById('mobile-nav');
function setMobileNav(open) {
    navToggle?.setAttribute('aria-expanded', String(open));
    mobileNav?.classList.toggle('is-open', open);
}
navToggle?.addEventListener('click', () => setMobileNav(navToggle.getAttribute('aria-expanded') !== 'true'));
mobileNav?.addEventListener('click', event => {if (event.target.closest('a')) setMobileNav(false);});
addEventListener('keydown', event => {
    if (event.key === 'Escape' && navToggle?.getAttribute('aria-expanded') === 'true') {
        setMobileNav(false);
        navToggle.focus();
    }
});
document.documentElement.classList.add('ks-js');
const homeHero = document.querySelector('.kl-hero');
const backTop = document.querySelector('.ks-back-top');
if(homeHero && backTop) new IntersectionObserver(([entry])=>backTop.toggleAttribute('data-past-hero', !entry.isIntersecting)).observe(homeHero);
import('./salon-motion').then(({initSalonMotion}) => initSalonMotion()).catch(() => {});
if (homeHero?.matches('[data-hero-slideshow]')) import('./salon-hero').then(({initSalonHero}) => initSalonHero()).catch(() => {});

const slider = document.querySelector('[data-inspiration-slider]');
if(slider){
    const buttons=[...document.querySelectorAll('[data-slide]')];
    const update=()=>buttons.forEach(button=>{button.disabled=Number(button.dataset.slide)<0 ? slider.scrollLeft<3 : slider.scrollLeft+slider.clientWidth>=slider.scrollWidth-3;});
    const slide=direction=>slider.scrollBy({left:direction*(slider.firstElementChild.getBoundingClientRect().width+18),behavior:matchMedia('(prefers-reduced-motion:reduce)').matches?'instant':'smooth'});
    buttons.forEach(button=>button.addEventListener('click',()=>slide(Number(button.dataset.slide))));
    slider.addEventListener('keydown',event=>{if(event.target!==slider)return;if(event.key==='ArrowRight'||event.key==='ArrowLeft'){event.preventDefault();slide(event.key==='ArrowRight'?1:-1);}});
    slider.addEventListener('scroll',update,{passive:true});new ResizeObserver(update).observe(slider);update();
}

for (const form of document.querySelectorAll('[data-salon-form]')) {
    const select = form.querySelector('[data-booking-service]');
    if (select) {
        function updateSummary() {
            const option = select.selectedOptions[0];
            const summary = form.querySelector('[data-selected-service]');
            summary.hidden = !select.value;
            summary.querySelector('[data-selected-name]').textContent = option?.textContent.split(' — ')[0] || '';
            summary.querySelector('[data-selected-price]').textContent = option?.dataset.price + (option?.dataset.gel ? ` · With gel polish ${option.dataset.gel}` : '');
        }
        select.addEventListener('change', updateSummary); updateSummary();
    }
    if (form.querySelector('button[type=submit]')?.disabled) continue;
    const params = new URLSearchParams(location.search);
    for (const key of ['source','medium','campaign','term','content']) {
        const value = params.get(`utm_${key}`);
        if (!value) continue;
        const input = document.createElement('input'); input.type='hidden'; input.name=`utm[${key}]`; input.value=value.slice(0,200); form.append(input);
    }
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type=submit]');
        const label = button.querySelector('[data-submit-label]');
        const status = form.querySelector('[data-form-status]');
        const previous = label.textContent;
        const body = new FormData(form);
        button.disabled = true; label.textContent = 'Sending your request…'; status.textContent = ''; status.classList.remove('is-error');
        form.setAttribute('aria-busy','true');
        try {
            const response = await fetch(form.action, {method:'POST', body, headers:{Accept:'application/json'}, credentials:'same-origin'});
            const data = await response.json().catch(()=>({}));
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || (response.status===429 ? 'Please wait a minute before trying again.' : 'We could not send your request. Please refresh this page or call the salon.'));
            status.textContent=data.message; form.reset(); select?.dispatchEvent(new Event('change')); status.focus();
        } catch (error) { status.textContent=error.message || 'Please try again or call the salon.'; status.classList.add('is-error'); status.focus(); }
        finally { button.disabled=false; label.textContent=previous; form.removeAttribute('aria-busy'); }
    });
}
