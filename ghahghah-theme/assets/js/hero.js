/**
 * Homepage banner slider — nav, progress, swipe / drag.
 */
(() => {
	'use strict';

	const root = document.querySelector('[data-ghahghah-hero-slider]');
	if (!root) {
		return;
	}

	const frame = root.querySelector('.ghahghah-hero__frame');
	const slides = Array.from(root.querySelectorAll('.ghahghah-hero__slide'));
	if (!frame || slides.length === 0) {
		return;
	}

	const prevButtons = Array.from(root.querySelectorAll('[data-ghahghah-hero-prev]'));
	const nextButtons = Array.from(root.querySelectorAll('[data-ghahghah-hero-next]'));
	const dots = Array.from(root.querySelectorAll('[data-ghahghah-hero-dot]'));
	const live = root.querySelector('[data-ghahghah-hero-live]');
	const progress = root.querySelector('[data-ghahghah-hero-progress]');
	const intervalMs = Math.max(3000, parseInt(root.getAttribute('data-interval') || '6000', 10) || 6000);
	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const multi = slides.length > 1;
	const swipeThreshold = 40;

	let index = 0;
	let timerId = 0;
	let startedAt = 0;
	let remaining = intervalMs;
	let paused = false;
	let pointerActive = false;
	let startX = 0;
	let startY = 0;
	let deltaX = 0;
	let dragging = false;
	let lockAxis = '';
	let suppressClick = false;

	const announce = (i) => {
		if (!live) {
			return;
		}
		live.textContent = `اسلاید ${i + 1} از ${slides.length}`;
	};

	const setProgress = (ratio) => {
		if (!progress) {
			return;
		}
		progress.style.transform = `scaleX(${Math.max(0, Math.min(1, ratio))})`;
	};

	const syncDots = () => {
		dots.forEach((dot, i) => {
			const active = i === index;
			dot.classList.toggle('is-active', active);
			dot.setAttribute('aria-selected', active ? 'true' : 'false');
		});
	};

	const clearTimer = () => {
		if (timerId) {
			window.clearTimeout(timerId);
			timerId = 0;
		}
	};

	const activate = (nextIndex, { restart = true } = {}) => {
		const total = slides.length;
		index = ((nextIndex % total) + total) % total;

		slides.forEach((slide, i) => {
			const active = i === index;
			slide.classList.toggle('is-active', active);
			// Keep in layout for crossfade — do not use HTML hidden attribute.
			slide.removeAttribute('hidden');
			slide.setAttribute('aria-hidden', active ? 'false' : 'true');
			if ('inert' in HTMLElement.prototype) {
				slide.inert = !active;
			}
		});

		syncDots();
		announce(index);

		if (restart && multi && !reduceMotion) {
			remaining = intervalMs;
			setProgress(0);
			startTimer();
		} else if (!multi || reduceMotion) {
			setProgress(0);
		}
	};

	const tick = () => {
		if (paused || reduceMotion || !multi) {
			return;
		}
		const elapsed = Date.now() - startedAt;
		const left = Math.max(0, remaining - elapsed);
		setProgress(1 - left / intervalMs);

		if (left <= 16) {
			activate(index + 1);
			return;
		}

		timerId = window.setTimeout(tick, 32);
	};

	const startTimer = () => {
		if (!multi || reduceMotion || paused) {
			return;
		}
		clearTimer();
		startedAt = Date.now();
		timerId = window.setTimeout(tick, 32);
	};

	const pause = () => {
		if (paused || !multi || reduceMotion) {
			return;
		}
		paused = true;
		remaining = Math.max(0, remaining - (Date.now() - startedAt));
		clearTimer();
		root.classList.add('is-paused');
	};

	const resume = () => {
		if (!paused || !multi || reduceMotion || pointerActive) {
			return;
		}
		paused = false;
		root.classList.remove('is-paused');
		startTimer();
	};

	prevButtons.forEach((btn) => {
		btn.addEventListener('click', (event) => {
			event.preventDefault();
			event.stopPropagation();
			activate(index - 1);
		});
	});

	nextButtons.forEach((btn) => {
		btn.addEventListener('click', (event) => {
			event.preventDefault();
			event.stopPropagation();
			activate(index + 1);
		});
	});

	dots.forEach((dot) => {
		dot.addEventListener('click', (event) => {
			event.preventDefault();
			event.stopPropagation();
			const target = parseInt(dot.getAttribute('data-index') || '', 10);
			if (Number.isNaN(target)) {
				return;
			}
			activate(target);
		});
	});

	root.addEventListener('keydown', (event) => {
		if (!multi) {
			return;
		}
		if (event.key === 'ArrowLeft') {
			event.preventDefault();
			activate(index + 1);
		} else if (event.key === 'ArrowRight') {
			event.preventDefault();
			activate(index - 1);
		}
	});

	root.addEventListener('mouseenter', pause);
	root.addEventListener('mouseleave', resume);
	root.addEventListener('focusin', pause);
	root.addEventListener('focusout', (event) => {
		if (!root.contains(event.relatedTarget)) {
			resume();
		}
	});

	document.addEventListener('visibilitychange', () => {
		if (document.hidden) {
			pause();
		} else {
			resume();
		}
	});

	const resetDrag = () => {
		pointerActive = false;
		dragging = false;
		lockAxis = '';
		deltaX = 0;
		frame.classList.remove('is-dragging');
	};

	const finishDrag = () => {
		if (!pointerActive) {
			return;
		}

		const moved = dragging && Math.abs(deltaX) >= swipeThreshold;
		if (moved && multi) {
			suppressClick = true;
			if (deltaX < 0) {
				activate(index + 1);
			} else {
				activate(index - 1);
			}
		} else {
			resume();
		}

		resetDrag();
		window.setTimeout(() => {
			suppressClick = false;
		}, 280);
	};

	frame.addEventListener(
		'pointerdown',
		(event) => {
			if (!multi || event.button > 0) {
				return;
			}
			if (event.target.closest('.ghahghah-hero__nav, .ghahghah-hero__ui, .ghahghah-hero__dots')) {
				return;
			}

			pointerActive = true;
			startX = event.clientX;
			startY = event.clientY;
			deltaX = 0;
			dragging = false;
			lockAxis = '';
			suppressClick = false;
			pause();

			try {
				frame.setPointerCapture(event.pointerId);
			} catch (err) {
				/* ignore */
			}
		},
		{ passive: true }
	);

	frame.addEventListener(
		'pointermove',
		(event) => {
			if (!pointerActive) {
				return;
			}

			deltaX = event.clientX - startX;
			const deltaY = event.clientY - startY;

			if (!lockAxis) {
				if (Math.abs(deltaX) < 10 && Math.abs(deltaY) < 10) {
					return;
				}
				lockAxis = Math.abs(deltaX) >= Math.abs(deltaY) ? 'x' : 'y';
				if (lockAxis === 'y') {
					resetDrag();
					resume();
					return;
				}
			}

			if (lockAxis === 'x') {
				dragging = true;
				frame.classList.add('is-dragging');
				event.preventDefault();
			}
		},
		{ passive: false }
	);

	frame.addEventListener('pointerup', finishDrag);
	frame.addEventListener('pointercancel', () => {
		resetDrag();
		resume();
	});

	frame.addEventListener(
		'click',
		(event) => {
			if (!suppressClick) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();
		},
		true
	);

	// Soften image drag ghosting.
	frame.querySelectorAll('img').forEach((img) => {
		img.setAttribute('draggable', 'false');
	});

	activate(0, { restart: true });
})();
