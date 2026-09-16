/**
 * Progressive enhancement for production steps accordion (mobile).
 * Without this script, all steps stay open and readable.
 */
(() => {
	'use strict';

	const root = document.querySelector('[data-ghahghah-steps]');
	if (!root) {
		return;
	}

	const detailsList = Array.from(root.querySelectorAll('[data-ghahghah-step-details]'));
	if (!detailsList.length) {
		return;
	}

	const mobileQuery = window.matchMedia('(max-width: 47.99rem)');
	let didInitMobile = false;

	const syncForViewport = () => {
		if (mobileQuery.matches) {
			if (!didInitMobile) {
				detailsList.forEach((el, index) => {
					el.open = index === 0;
				});
				didInitMobile = true;
			}
			return;
		}

		didInitMobile = false;
		detailsList.forEach((el) => {
			el.open = true;
		});
	};

	syncForViewport();

	if (typeof mobileQuery.addEventListener === 'function') {
		mobileQuery.addEventListener('change', syncForViewport);
	} else if (typeof mobileQuery.addListener === 'function') {
		mobileQuery.addListener(syncForViewport);
	}
})();
