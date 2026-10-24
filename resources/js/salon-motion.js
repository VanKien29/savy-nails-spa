import Lenis from 'lenis';

export function initSalonMotion() {
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    const desktop = matchMedia('(min-width: 960px) and (hover: hover) and (pointer: fine)');
    const enabled = document.body.dataset.animations === 'on';
    const economical = navigator.connection?.saveData || (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4);
    const targets = [...document.querySelectorAll('[data-animate]')];
    const artwork = [...document.querySelectorAll('[data-draw-art]')];
    const progressLines = [...document.querySelectorAll('[data-scroll-line]')];
    const hero = document.querySelector('.kl-hero[data-parallax]');
    const activeLines = new Set();
    let lenis;
    let reveals;
    let visibility;
    let heroVisible = false;
    let frame;
    let headerScrolled = false;

    document.querySelector('.ks-back-top')?.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        if (lenis) lenis.scrollTo(0);
        else window.scrollTo({top: 0, behavior: reduced.matches ? 'instant' : 'smooth'});
    });

    function reset() {
        lenis?.destroy();
        lenis = undefined;
        reveals?.disconnect();
        visibility?.disconnect();
        activeLines.clear();
        cancelAnimationFrame(frame);
        frame = undefined;
        targets.forEach(element => {
            element.classList.remove('ks-reveal-pending');
            element.getAnimations().forEach(animation => animation.cancel());
        });
        artwork.forEach(element => element.classList.add('is-drawn'));
        progressLines.forEach(element => element.style.setProperty('--kl-progress', '1'));
        document.documentElement.classList.remove('ks-motion');
        hero?.style.removeProperty('--ks-parallax');
    }

    function updateScroll() {
        frame = undefined;
        if (!enabled || reduced.matches) return;
        const scrolled = scrollY > 40;
        if (scrolled !== headerScrolled) {
            document.documentElement.classList.toggle('ks-has-scrolled', scrolled);
            headerScrolled = scrolled;
        }
        if (desktop.matches && !economical && heroVisible) {
            hero?.style.setProperty('--ks-parallax', `${Math.min(scrollY * .045, 24)}px`);
        }
        if (!economical) activeLines.forEach(element => {
            const rect = element.parentElement.getBoundingClientRect();
            const progress = Math.max(0, Math.min(1, (innerHeight - rect.top) / (innerHeight * .75)));
            element.style.setProperty('--kl-progress', progress.toFixed(3));
        });
    }

    function start() {
        reset();
        if (!enabled || reduced.matches) return;
        document.documentElement.classList.add('ks-motion');
        if (desktop.matches && !economical) {
            lenis = new Lenis({
                autoRaf: true,
                lerp: .095,
                smoothWheel: true,
                anchors: {offset: -110},
                // Keep horizontal rails and filter bars native so a wheel over a photo
                // can still continue the document's vertical scroll.
                prevent: node => !!node.closest('dialog'),
            });
        }
        reveals = new IntersectionObserver(entries => entries.forEach(({target, isIntersecting}) => {
            if (!isIntersecting) return;
            target.classList.remove('ks-reveal-pending');
            const direction = target.dataset.animate;
            const distance = desktop.matches && !economical ? 28 : 12;
            const transform = direction === 'slide-left' ? `translateX(${distance}px)` : direction === 'slide-right' ? `translateX(-${distance}px)` : `translateY(${distance}px)`;
            target.animate([{opacity: 0, transform}, {opacity: 1, transform: 'translate(0,0)'}], {
                duration: desktop.matches && !economical ? 700 : 380,
                delay: Number(target.dataset.stagger || 0),
                easing: 'cubic-bezier(.16,1,.3,1)',
            });
            reveals.unobserve(target);
        }), {threshold: .06, rootMargin: '0px 0px -12px 0px'});
        targets.forEach(target => {
            if (target.getBoundingClientRect().top > innerHeight) {
                target.classList.add('ks-reveal-pending');
                reveals.observe(target);
            }
        });
        visibility = new IntersectionObserver(entries => entries.forEach(({target, isIntersecting}) => {
            if (target === hero) heroVisible = isIntersecting;
            if (target.hasAttribute('data-draw-art') && isIntersecting) {
                target.classList.add('is-drawn');
                visibility.unobserve(target);
            }
            if (target.hasAttribute('data-scroll-line')) {
                if (isIntersecting) activeLines.add(target);
                else activeLines.delete(target);
            }
        }), {rootMargin: '20px'});
        artwork.forEach(element => {
            element.classList.remove('is-drawn');
            visibility.observe(element);
        });
        progressLines.forEach(element => visibility.observe(element));
        if (hero) visibility.observe(hero);
        updateScroll();
    }

    start();
    reduced.addEventListener('change', start);
    desktop.addEventListener('change', start);
    addEventListener('scroll', () => {
        if (frame === undefined && enabled && !reduced.matches) frame = requestAnimationFrame(updateScroll);
    }, {passive: true});
    addEventListener('pagehide', reset);
    addEventListener('pageshow', event => {if (event.persisted) start();});
}
