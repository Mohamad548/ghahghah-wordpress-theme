/**
 * Single article — smooth ToC scroll offset handled by CSS; keep file for future hooks.
 */
(function () {
	'use strict';

	var links = document.querySelectorAll('.ghahghah-single-article__toc-nav a[href^="#"]');
	if (!links.length) {
		return;
	}

	links.forEach(function (link) {
		link.addEventListener('click', function () {
			var details = link.closest('details');
			if (details && window.matchMedia('(max-width: 47.99rem)').matches) {
				details.open = false;
			}
		});
	});
})();
