/**
 * Footer back-to-top: smooth scroll with reduced-motion respect.
 */
(() => {
	'use strict';

	const btn = document.querySelector('[data-ghahghah-back-to-top]');
	if (!btn) {
		return;
	}

	btn.addEventListener('click', (event) => {
		const href = btn.getAttribute('href') || '';
		if (!href.startsWith('#')) {
			return;
		}
		const id = href.slice(1);
		const target = id ? document.getElementById(id) : null;
		if (!target) {
			return;
		}
		event.preventDefault();
		const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
		if (typeof target.focus === 'function') {
			const prev = target.getAttribute('tabindex');
			if (prev === null) {
				target.setAttribute('tabindex', '-1');
			}
			target.focus({ preventScroll: true });
			if (prev === null) {
				target.addEventListener(
					'blur',
					() => {
						target.removeAttribute('tabindex');
					},
					{ once: true }
				);
			}
		}
	});
})();
