const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');

export function initSalonHero() {
    const hero = document.querySelector('[data-hero-slideshow]');
    if (!hero) return;
    const slides = [...hero.querySelectorAll('[data-hero-slide]')];
    if (slides.length < 2) return;
    const dots = [...hero.querySelectorAll('[data-hero-dot]')];
    let index = 0;
    let timer = 0;
    let paused = reduceMotion.matches || navigator.connection?.saveData === true;
    let touchStart = null;

    const render = (nextIndex) => {
        index = (nextIndex + slides.length) % slides.length;
        slides.forEach((slide, position) => {
            const active = position === index;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', String(!active));
        });
        dots.forEach((dot, position) => dot.setAttribute('aria-pressed', String(position === index)));
    };
    const stop = () => { window.clearInterval(timer); timer = 0; };
    const start = () => {
        stop();
        if (!paused && !reduceMotion.matches && document.visibilityState === 'visible') timer = window.setInterval(() => render(index + 1), 6500);
    };
    const move = direction => { render(index + direction); start(); };
    dots.forEach((dot, position) => dot.addEventListener('click', () => { render(position); start(); }));
    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', start);
    hero.addEventListener('focusin', stop);
    hero.addEventListener('focusout', event => { if (!hero.contains(event.relatedTarget)) start(); });
    hero.addEventListener('touchstart', event => { touchStart = event.changedTouches[0]?.clientX ?? null; }, {passive: true});
    hero.addEventListener('touchend', event => {
        if (touchStart === null) return;
        const distance = event.changedTouches[0].clientX - touchStart;
        touchStart = null;
        if (Math.abs(distance) > 45) move(distance < 0 ? 1 : -1);
    }, {passive: true});
    document.addEventListener('visibilitychange', start);
    reduceMotion.addEventListener?.('change', event => { paused = event.matches; start(); });
    render(0);
    start();
}
