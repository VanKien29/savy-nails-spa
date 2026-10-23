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

const menu = document.querySelector('[data-service-menu]');
if (menu) {
    const links = [...menu.querySelectorAll('[data-service-category]')];
    const groups = [...menu.querySelectorAll('[data-menu-category]')];
    let category = menu.dataset.activeCategory || 'nails';
    let expanded = false;
    const more = menu.querySelector('[data-menu-more]');
    const mobile = matchMedia('(max-width:730px)');
    const adjustGroups=()=>groups.forEach(group => group.querySelectorAll('.ks-price-group').forEach((part,index)=>{part.open=!mobile.matches || index===0;}));
    adjustGroups();mobile.addEventListener('change',adjustGroups);
    function filter(animate = false) {
        let visible = 0;
        groups.forEach(group => {
            const rows = [...group.querySelectorAll('[data-service-row]')];
            rows.forEach((row,index) => {
                row.hidden = group.dataset.menuCategory==='pedicures' && !expanded && index>=6;
            });
            group.hidden = category !== group.dataset.menuCategory;
            if (!group.hidden) visible += rows.length;
            if(animate && !group.hidden && !matchMedia('(prefers-reduced-motion:reduce)').matches) group.animate([{opacity:0,transform:'translateY(16px)'},{opacity:1,transform:'translateY(0)'}],{duration:420,easing:'cubic-bezier(.2,.7,.2,1)'});
        });
        menu.querySelector('[data-menu-empty]').hidden = visible > 0;
        more.hidden = category!=='pedicures' || expanded;
        links.forEach(link => {
            if (link.dataset.serviceCategory === category) link.setAttribute('aria-current', 'true');
            else link.removeAttribute('aria-current');
        });
    }
    links.forEach(link => link.addEventListener('click', event => {
        event.preventDefault(); category = link.dataset.serviceCategory; expanded=false; filter(true);
        link.scrollIntoView({block:'nearest',inline:'nearest',behavior:matchMedia('(prefers-reduced-motion:reduce)').matches?'instant':'smooth'});
        history.replaceState(null, '', link.href);
    }));
    more.addEventListener('click',()=>{expanded=true;filter(true);});
    addEventListener('popstate',()=>{const value=new URLSearchParams(location.search).get('category');category=links.some(link=>link.dataset.serviceCategory===value)?value:'nails';expanded=false;filter(true);});
    filter();
}

const gallery = document.querySelector('[data-gallery]');
if (gallery) {
    gallery.querySelectorAll('[data-gallery-filter]').forEach(button => button.addEventListener('click', () => {
        const category = button.dataset.galleryFilter;
        gallery.querySelectorAll('[data-gallery-category]').forEach(item => { item.hidden = category !== 'all' && item.dataset.galleryCategory !== category; });
        gallery.querySelectorAll('[data-gallery-filter]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
    }));
}

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
