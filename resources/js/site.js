if (document.body.dataset.animations === 'on' && !document.body.classList.contains('ks-site') && document.querySelector('[data-animate], [data-count], [data-parallax], [data-scroll-progress]')) {
    import('./animations').then(({initAnimations}) => initAnimations()).catch(() => {});
}

for (const button of document.querySelectorAll('[data-open-dialog]')) {
    button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal());
}
for (const button of document.querySelectorAll('[data-close-dialog]')) button.addEventListener('click', () => button.closest('dialog')?.close());
const lightbox = document.getElementById('image-lightbox');
for (const button of document.querySelectorAll('[data-lightbox]')) {
    button.addEventListener('click', () => {
        if (!lightbox) return;
        lightbox.querySelector('img').src = button.dataset.lightbox;
        lightbox.querySelector('img').alt = button.dataset.caption || '';
        lightbox.querySelector('p').textContent = button.dataset.caption || '';
        lightbox.showModal();
    });
}
for (const dialog of document.querySelectorAll('dialog')) dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });

for (const group of document.querySelectorAll('[data-tabs]')) {
    const buttons = [...group.querySelectorAll('[data-tab-index]')];
    const panels = [...group.querySelectorAll('[data-tab-panel]')];
    group.querySelector('.tab-buttons').setAttribute('role', 'tablist');
    function activate(index, focus = false) {
        buttons.forEach((button, i) => {
            button.setAttribute('role', 'tab'); button.setAttribute('aria-controls', panels[i].id);
            button.setAttribute('aria-selected', String(i === index)); button.tabIndex = i === index ? 0 : -1;
            panels[i].setAttribute('role', 'tabpanel'); panels[i].setAttribute('aria-labelledby', button.id); panels[i].hidden = i !== index;
        });
        if (focus) buttons[index].focus();
    }
    buttons.forEach((button, i) => {
        button.addEventListener('click', () => activate(i));
        button.addEventListener('keydown', e => {
            let index;
            if (e.key === 'ArrowRight') index = (i + 1) % buttons.length;
            if (e.key === 'ArrowLeft') index = (i - 1 + buttons.length) % buttons.length;
            if (e.key === 'Home') index = 0;
            if (e.key === 'End') index = buttons.length - 1;
            if (index !== undefined) { e.preventDefault(); activate(index, true); }
        });
    });
    activate(0);
}

for (const form of document.querySelectorAll('[data-lead-form]')) {
    if (form.querySelector('button[type=submit]')?.disabled) continue;
    const params = new URLSearchParams(location.search);
    for (const field of ['source','medium','campaign','term','content']) {
        const value = params.get(`utm_${field}`);
        if (value) { const input = document.createElement('input'); input.type='hidden'; input.name=`utm[${field}]`; input.value=value.slice(0,200); form.append(input); }
    }
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type=submit]');
        const status = form.querySelector('[data-form-status]');
        button.disabled = true; status.textContent = 'Đang gửi yêu cầu…';
        try {
            const response = await fetch(form.action, {method:'POST', body:new FormData(form), headers:{Accept:'application/json'}, credentials:'same-origin'});
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || 'Không gửi được. Vui lòng tải lại trang và thử lại.');
            status.textContent = data.message; form.reset(); status.focus();
        } catch (error) { status.textContent = error.message || 'Lỗi kết nối. Vui lòng thử lại.'; status.focus(); }
        finally { button.disabled = false; }
    });
}
if (document.body.dataset.tracking) import('./tracking').then(({initTracking}) => initTracking()).catch(() => {});
