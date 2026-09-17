/**
 * Blog archive — progressive enhancements for toolbar.
 */
(function () {
	'use strict';

	var form = document.querySelector('[data-ghahghah-ba-toolbar]');
	if (!form) {
		return;
	}

	var sort = form.querySelector('[data-ghahghah-ba-sort]');
	if (sort) {
		sort.addEventListener('change', function () {
			form.requestSubmit ? form.requestSubmit() : form.submit();
		});
	}

	var search = form.querySelector('input[type="search"]');
	if (!search) {
		return;
	}

	var timer = null;
	search.addEventListener('input', function () {
		if (timer) {
			window.clearTimeout(timer);
		}
		timer = window.setTimeout(function () {
			form.requestSubmit ? form.requestSubmit() : form.submit();
		}, 450);
	});
})();
