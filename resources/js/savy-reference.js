const toggle = document.querySelector('.av-nav-toggle');
const nav = document.querySelector('.av-nav');
const header = document.querySelector('.av-header');
const progress = document.querySelector('.av-scroll-progress span');
const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

document.querySelectorAll('[data-auto-map]').forEach(frame => {
    const loadMap = () => {
        frame.src = frame.dataset.autoMap;
        frame.removeAttribute('data-auto-map');
    };
    if (!('IntersectionObserver' in window)) {
        loadMap();
        return;
    }
    const observer = new IntersectionObserver(entries => {
        if (!entries.some(entry => entry.isIntersecting)) return;
        loadMap();
        observer.disconnect();
    }, {rootMargin: '300px'});
    observer.observe(frame);
});

if (!reduced) document.documentElement.classList.add('js-motion');

toggle?.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(open));
    nav.classList.toggle('is-open', open);
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && toggle?.getAttribute('aria-expanded') === 'true') {
        toggle.setAttribute('aria-expanded', 'false');
        nav.classList.remove('is-open');
        toggle.focus();
    }
});

const updateScrollEffects = () => {
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    const ratio = maxScroll > 0 ? Math.min(1, Math.max(0, window.scrollY / maxScroll)) : 0;
    header?.classList.toggle('is-scrolled', window.scrollY > 16);
    if (progress) progress.style.transform = `scaleX(${ratio})`;
};
let scrollFrame;
window.addEventListener('scroll', () => {
    if (scrollFrame) return;
    scrollFrame = requestAnimationFrame(() => {
        updateScrollEffects();
        scrollFrame = undefined;
    });
}, {passive: true});
updateScrollEffects();

document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => anchor.addEventListener('click', event => {
    const target = document.querySelector(anchor.getAttribute('href'));
    if (!target) return;
    event.preventDefault();
    const top = target.getBoundingClientRect().top + window.scrollY - (header?.offsetHeight ?? 0) - 16;
    window.scrollTo({top, behavior: reduced ? 'instant' : 'smooth'});
    history.replaceState(null, '', anchor.getAttribute('href'));
}));

if (!reduced && 'IntersectionObserver' in window) {
    document.querySelectorAll('[data-reveal], .av-menu-panel, .av-feature, .av-gallery-photo, .av-faq details, .av-contact-grid > *, .av-footer-grid > *').forEach(element => {
        if (element.getBoundingClientRect().top >= innerHeight) element.classList.add('av-motion-item');
    });
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('av-revealing');
            observer.unobserve(entry.target);
        }
    }), {threshold: 0.12});
    document.querySelectorAll('.av-motion-item').forEach((element, index) => {
        element.style.setProperty('--av-delay', `${Math.min(index % 5, 4) * 70}ms`);
        observer.observe(element);
    });
}
const rail = document.querySelector('.av-gallery-rail');
const move = direction => rail?.scrollBy({left: direction * rail.clientWidth, behavior: reduced ? 'instant' : 'smooth'});
document.querySelectorAll('[data-carousel]').forEach(button => button.addEventListener('click', () => move(Number(button.dataset.carousel))));
rail?.addEventListener('keydown', event => {
    if (['ArrowLeft', 'ArrowRight'].includes(event.key)) {event.preventDefault(); move(event.key === 'ArrowLeft' ? -1 : 1);}
});
if (rail && !reduced && !navigator.connection?.saveData) {
    let paused = false;
    rail.addEventListener('mouseenter', () => paused = true);
    rail.addEventListener('mouseleave', () => paused = false);
    rail.addEventListener('focusin', () => paused = true);
    rail.addEventListener('focusout', () => paused = false);
    setInterval(() => {
        if (paused || document.hidden) return;
        const atEnd = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 5;
        rail.scrollTo({left: atEnd ? 0 : rail.scrollLeft + rail.clientWidth, behavior: 'smooth'});
    }, 5000);
}
const dialog = document.querySelector('.av-lightbox');
let trigger;
document.querySelectorAll('[data-lightbox-file]').forEach(button => button.addEventListener('click', () => {
    trigger = button;
    const source = button.querySelector('img');
    dialog.querySelector('img').src = source.currentSrc || source.src;
    dialog.querySelector('img').alt = source.alt;
    dialog.showModal();
}));
dialog?.querySelector('.av-dialog-close').addEventListener('click', () => dialog.close());
dialog?.addEventListener('click', event => {if (event.target === dialog) dialog.close();});
dialog?.addEventListener('close', () => trigger?.focus());
document.querySelector('.av-back-top')?.addEventListener('click', event => {
    event.preventDefault();
    window.scrollTo({top: 0, behavior: reduced ? 'instant' : 'smooth'});
});
