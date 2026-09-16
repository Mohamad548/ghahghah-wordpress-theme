/**
 * Mobile bottom nav: measure height, hide with header drawer / form fields.
 */
(() => {
	'use strict';

	const nav = document.querySelector('[data-ghahghah-bottom-nav]');
	if (!nav) {
		return;
	}

	const root = document.documentElement;
	const mq = window.matchMedia('(max-width: 767.98px)');
	let formHide = false;
	let formBlurTimer = null;

	const isDrawerOpen = () => document.body.classList.contains('ghahghah-drawer-open');

	const applyVisibility = () => {
		const shouldHide = isDrawerOpen() || formHide;
		if (shouldHide) {
			nav.setAttribute('hidden', '');
		} else {
			nav.removeAttribute('hidden');
		}
	};

	const measure = () => {
		if (!mq.matches || nav.hasAttribute('hidden')) {
			return;
		}
		const height = Math.ceil(nav.getBoundingClientRect().height);
		if (height > 0) {
			root.style.setProperty('--gg-nav-height', `${height}px`);
		}
	};

	const isFormField = (el) => {
		if (!el || el.nodeType !== 1) {
			return false;
		}
		const tag = el.tagName;
		if (tag === 'TEXTAREA' || tag === 'SELECT') {
			return true;
		}
		if (tag === 'INPUT') {
			const type = (el.getAttribute('type') || 'text').toLowerCase();
			const blocked = ['button', 'submit', 'reset', 'checkbox', 'radio', 'file', 'hidden', 'image', 'range', 'color'];
			return !blocked.includes(type);
		}
		return el.isContentEditable;
	};

	document.addEventListener(
		'focusin',
		(event) => {
			if (!mq.matches || !isFormField(event.target)) {
				return;
			}
			if (formBlurTimer) {
				window.clearTimeout(formBlurTimer);
				formBlurTimer = null;
			}
			formHide = true;
			applyVisibility();
		},
		true
	);

	document.addEventListener(
		'focusout',
		(event) => {
			if (!formHide) {
				return;
			}
			if (formBlurTimer) {
				window.clearTimeout(formBlurTimer);
			}
			formBlurTimer = window.setTimeout(() => {
				formBlurTimer = null;
				const next = document.activeElement;
				if (isFormField(next)) {
					return;
				}
				formHide = false;
				applyVisibility();
				measure();
			}, 80);
		},
		true
	);

	const drawerObserver = new MutationObserver(() => {
		applyVisibility();
		if (!nav.hasAttribute('hidden')) {
			measure();
		}
	});
	drawerObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });

	if ('ResizeObserver' in window) {
		new ResizeObserver(measure).observe(nav);
	} else {
		window.addEventListener('resize', measure, { passive: true });
	}

	mq.addEventListener('change', () => {
		formHide = false;
		applyVisibility();
		measure();
	});

	applyVisibility();
	measure();
})();
