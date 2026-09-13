/**
 * Lightweight theme interactions (no jQuery).
 */
(() => {
	'use strict';

	const toggle = document.querySelector('[data-ghahghah-nav-toggle]');
	const panel = document.querySelector('[data-ghahghah-nav-panel]');

	if (!toggle || !panel) {
		return;
	}

	const mq = window.matchMedia('(min-width: 48rem)');

	const syncNav = () => {
		const desktop = mq.matches;
		if (desktop) {
			panel.hidden = false;
			toggle.setAttribute('aria-expanded', 'false');
			return;
		}

		const expanded = toggle.getAttribute('aria-expanded') === 'true';
		panel.hidden = !expanded;
	};

	toggle.addEventListener('click', () => {
		const expanded = toggle.getAttribute('aria-expanded') === 'true';
		toggle.setAttribute('aria-expanded', String(!expanded));
		panel.hidden = expanded;
	});

	mq.addEventListener('change', syncNav);
	syncNav();
})();
