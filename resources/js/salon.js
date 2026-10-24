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

// Touch stays native; mouse/pen dragging also works in narrow desktop previews.
for (const rail of document.querySelectorAll('.ks-menu-tabs, .ks-gallery-filters')) {
    let drag;
    let suppressClick = false;
    rail.tabIndex = 0;
    rail.setAttribute('data-lenis-prevent', '');
    rail.addEventListener('dragstart', event => event.preventDefault());
    rail.addEventListener('pointerdown', event => {
        suppressClick = false;
        if (event.pointerType === 'touch' || event.button !== 0 || rail.scrollWidth <= rail.clientWidth) return;
        drag = {id: event.pointerId, x: event.clientX, y: event.clientY, left: rail.scrollLeft, active: false};
    });
    rail.addEventListener('pointermove', event => {
        if (!drag || event.pointerId !== drag.id) return;
        const dx = event.clientX - drag.x;
        const dy = event.clientY - drag.y;
        if (!drag.active) {
            if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 6) {drag = undefined; return;}
            if (Math.abs(dx) < 6) return;
            drag.active = true;
            suppressClick = true;
            rail.setPointerCapture(event.pointerId);
            rail.classList.add('is-dragging');
        }
        event.preventDefault();
        rail.scrollLeft = drag.left - dx;
    });
    const finishDrag = () => {drag = undefined; rail.classList.remove('is-dragging');};
    rail.addEventListener('pointerup', finishDrag);
    rail.addEventListener('pointercancel', finishDrag);
    rail.addEventListener('lostpointercapture', finishDrag);
    rail.addEventListener('click', event => {
        if (!suppressClick) return;
        event.preventDefault(); event.stopImmediatePropagation(); suppressClick = false;
    }, true);
    rail.addEventListener('keydown', event => {
        suppressClick = false;
        if (event.target !== rail || !['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        rail.scrollBy({left: (event.key === 'ArrowRight' ? 1 : -1) * 160, behavior: matchMedia('(prefers-reduced-motion:reduce)').matches ? 'instant' : 'smooth'});
    });
}

const menu = document.querySelector('[data-service-menu]');
if (menu) {
    const links = [...menu.querySelectorAll('[data-service-category]')];
    const groups = [...menu.querySelectorAll('[data-menu-category]')];
    let category = menu.dataset.activeCategory || 'nails';
    function filter(animate = false) {
        let visible = 0;
        groups.forEach(group => {
            group.hidden = category !== group.dataset.menuCategory;
            if (!group.hidden) visible += group.querySelectorAll('[data-service-row]').length;
            if(animate && !group.hidden && !matchMedia('(prefers-reduced-motion:reduce)').matches) group.animate([{opacity:0,transform:'translateY(16px)'},{opacity:1,transform:'translateY(0)'}],{duration:420,easing:'cubic-bezier(.2,.7,.2,1)'});
        });
        menu.querySelector('[data-menu-empty]').hidden = visible > 0;
        links.forEach(link => {
            if (link.dataset.serviceCategory === category) link.setAttribute('aria-current', 'true');
            else link.removeAttribute('aria-current');
        });
    }
    links.forEach(link => link.addEventListener('click', event => {
        event.preventDefault(); category = link.dataset.serviceCategory; filter(true);
        link.scrollIntoView({block:'nearest',inline:'nearest',behavior:matchMedia('(prefers-reduced-motion:reduce)').matches?'instant':'smooth'});
        history.replaceState(null, '', link.href);
    }));
    addEventListener('popstate',()=>{const value=new URLSearchParams(location.search).get('category');category=links.some(link=>link.dataset.serviceCategory===value)?value:'nails';filter(true);});
    filter();
    const selectedLink = links.find(link => link.dataset.serviceCategory === category);
    if (selectedLink) {
        const rail = selectedLink.parentElement;
        const selected = selectedLink.getBoundingClientRect();
        const bounds = rail.getBoundingClientRect();
        if (selected.left < bounds.left) rail.scrollLeft -= bounds.left - selected.left;
        else if (selected.right > bounds.right) rail.scrollLeft += selected.right - bounds.right;
    }
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
