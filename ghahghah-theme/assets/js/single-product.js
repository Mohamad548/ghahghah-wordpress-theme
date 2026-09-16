/**
 * Single product gallery thumbs / dots.
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-ghahghah-sp-gallery]');
	if (!root) {
		return;
	}

	var slides = root.querySelectorAll('.ghahghah-single-product__gallery-slide');
	var thumbs = root.querySelectorAll('[data-ghahghah-sp-thumb]');
	var dots = root.querySelectorAll('.ghahghah-single-product__dot');

	function activate(index) {
		slides.forEach(function (slide, i) {
			var on = i === index;
			slide.classList.toggle('is-active', on);
			if (on) {
				slide.removeAttribute('hidden');
			} else {
				slide.setAttribute('hidden', '');
			}
		});
		thumbs.forEach(function (thumb, i) {
			var on = i === index;
			thumb.classList.toggle('is-active', on);
			thumb.setAttribute('aria-selected', on ? 'true' : 'false');
		});
		dots.forEach(function (dot, i) {
			dot.classList.toggle('is-active', i === index);
		});
	}

	thumbs.forEach(function (thumb) {
		thumb.addEventListener('click', function () {
			var index = parseInt(thumb.getAttribute('data-ghahghah-sp-thumb') || '0', 10);
			activate(index);
		});
	});
})();
