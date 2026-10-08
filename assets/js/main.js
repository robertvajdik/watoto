// Malezi na Watoto — public JS
(function () {
    // Mobile nav toggle
    const navToggle = document.getElementById('navToggle');
    const siteNav = document.getElementById('siteNav');
    if (navToggle && siteNav) {
        const setNavOpen = (open) => {
            document.body.classList.toggle('nav-open', open);
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        navToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            setNavOpen(!document.body.classList.contains('nav-open'));
        });
        siteNav.addEventListener('click', (e) => {
            if (e.target.closest('a')) setNavOpen(false);
        });
        document.addEventListener('click', (e) => {
            if (!document.body.classList.contains('nav-open')) return;
            if (siteNav.contains(e.target) || navToggle.contains(e.target)) return;
            setNavOpen(false);
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.body.classList.contains('nav-open')) {
                setNavOpen(false);
                navToggle.focus();
            }
        });
    }

    // Gallery filter
    const filterBar = document.querySelector('.gallery-filter');
    const grid = document.getElementById('galleryGrid');
    if (filterBar && grid) {
        filterBar.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            filterBar.querySelectorAll('button').forEach(b => {
                b.classList.toggle('active', b === btn);
                b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
            });
            const f = btn.dataset.filter;
            grid.querySelectorAll('.gallery-item').forEach(item => {
                item.style.display = (f === 'all' || item.dataset.category === f) ? '' : 'none';
            });
        });
    }

    // reCAPTCHA v3 — mint a fresh token on any form that carries a token field.
    if (window.WMG_RECAPTCHA_KEY && window.grecaptcha !== undefined) {
        document.querySelectorAll('input[name="recaptcha_token"][data-recaptcha-action]').forEach(input => {
            const form = input.form;
            if (!form || form.dataset.recaptchaBound) return;
            form.dataset.recaptchaBound = '1';
            form.addEventListener('submit', function (e) {
                if (input.value) return; // token already set (post-execute submit)
                e.preventDefault();
                const action = input.dataset.recaptchaAction || 'submit';
                grecaptcha.ready(function () {
                    grecaptcha.execute(window.WMG_RECAPTCHA_KEY, { action: action })
                        .then(function (token) {
                            input.value = token;
                            form.submit();
                        })
                        .catch(function () { form.submit(); });
                });
            });
        });
    }
})();
