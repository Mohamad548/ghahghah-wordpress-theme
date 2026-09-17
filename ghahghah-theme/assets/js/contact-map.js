/**
 * Contact map facade — load Google Maps iframe only after user intent.
 */
(() => {
	'use strict';

	const root = document.querySelector('[data-ghahghah-map-facade]');
	if (!root) {
		return;
	}

	const button = root.querySelector('[data-ghahghah-map-load]');
	const frame = root.querySelector('[data-ghahghah-map-frame]');
	if (!button || !frame) {
		return;
	}

	const src = frame.getAttribute('data-src') || '';
	if (!src) {
		return;
	}

	const activate = () => {
		if (frame.getAttribute('src')) {
			return;
		}
		frame.setAttribute('src', src);
		root.classList.add('is-loaded');
		button.setAttribute('hidden', '');
		frame.removeAttribute('hidden');
		frame.focus({ preventScroll: true });
	};

	button.addEventListener('click', (event) => {
		event.preventDefault();
		activate();
	});
})();
