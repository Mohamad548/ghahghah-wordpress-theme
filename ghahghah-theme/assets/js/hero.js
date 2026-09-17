/**
 * Homepage banner slider — nav, progress, swipe / drag.
 * Lazy-hydrates deferred slides; last requested destination wins.
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
	const decodeTimeoutMs = 4000;

	/** Currently visible / committed slide. */
	let index = 0;
	/** Latest destination the user (or timer) asked for. */
	let requestedIndex = 0;
	/** Monotonic token — only the latest request may commit UI. */
	let requestToken = 0;
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

	const normalize = (nextIndex) => {
		const total = slides.length;
		return ((nextIndex % total) + total) % total;
	};

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
		progress.classList.remove('is-running');
		progress.style.animationDuration = '';
		progress.style.transform = `scaleX(${Math.max(0, Math.min(1, ratio))})`;
	};

	const startProgressAnimation = (durationMs) => {
		if (!progress || reduceMotion) {
			return;
		}
		progress.classList.remove('is-running');
		// Force restart CSS animation.
		void progress.offsetWidth;
		progress.style.animationDuration = `${Math.max(1, durationMs)}ms`;
		progress.style.transform = '';
		progress.classList.add('is-running');
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
		if (progress) {
			progress.classList.remove('is-running');
			progress.style.animationDuration = '';
		}
	};

	/**
	 * Apply deferred picture sources for a slide (no-op if already hydrated).
	 */
	const hydrateSlide = (slide) => {
		if (!slide || slide.dataset.ghahghahHeroHydrated === '1') {
			return;
		}
		const source = slide.querySelector('source');
		const img = slide.querySelector('img');
		if (source && source.dataset.ghahghahHeroSrcset) {
			source.setAttribute('srcset', source.dataset.ghahghahHeroSrcset);
			delete source.dataset.ghahghahHeroSrcset;
		}
		if (img) {
			if (img.dataset.ghahghahHeroSrcset) {
				img.setAttribute('srcset', img.dataset.ghahghahHeroSrcset);
				delete img.dataset.ghahghahHeroSrcset;
			}
			if (img.dataset.ghahghahHeroSrc) {
				img.setAttribute('src', img.dataset.ghahghahHeroSrc);
				delete img.dataset.ghahghahHeroSrc;
			}
		}
		slide.dataset.ghahghahHeroHydrated = '1';
	};

	/**
	 * @param {Element|null|undefined} slide
	 * @returns {Promise<boolean>} true only on successful load+decode
	 */
	const waitDecoded = (slide) => {
		const img = slide?.querySelector('img');
		if (!img) {
			return Promise.resolve(false);
		}
		if (img.complete && img.naturalWidth > 0) {
			if (!img.decode) {
				return Promise.resolve(true);
			}
			return img
				.decode()
				.then(() => true)
				.catch(() => false);
		}

		return new Promise((resolve) => {
			let settled = false;
			let timeoutId = 0;

			const cleanup = () => {
				img.removeEventListener('load', onLoad);
				img.removeEventListener('error', onError);
				if (timeoutId) {
					window.clearTimeout(timeoutId);
					timeoutId = 0;
				}
			};

			const finish = (ok) => {
				if (settled) {
					return;
				}
				settled = true;
				cleanup();
				resolve(ok);
			};

			const onLoad = () => {
				if (img.naturalWidth <= 0) {
					finish(false);
					return;
				}
				if (!img.decode) {
					finish(true);
					return;
				}
				img.decode().then(() => finish(true)).catch(() => finish(false));
			};

			const onError = () => {
				finish(false);
			};

			img.addEventListener('load', onLoad);
			img.addEventListener('error', onError);
			timeoutId = window.setTimeout(() => finish(false), decodeTimeoutMs);

			// Race: may have completed between the early check and listener attach.
			if (img.complete) {
				if (img.naturalWidth > 0) {
					onLoad();
				} else {
					finish(false);
				}
			}
		});
	};

	const applyActiveClasses = () => {
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
	};

	const restartTimerIfNeeded = (restart) => {
		if (restart && multi && !reduceMotion && !paused) {
			remaining = intervalMs;
			setProgress(0);
			startTimer();
		} else if (!multi || reduceMotion) {
			setProgress(0);
		}
	};

	/**
	 * Request a destination. UI commits only after that destination is ready,
	 * and only if this request is still the latest (token).
	 */
	const activate = (nextIndex, { restart = true } = {}) => {
		const target = normalize(nextIndex);
		requestedIndex = target;
		const token = ++requestToken;

		// Pause autoplay while a destination is pending — avoid stacked timers.
		clearTimer();

		const run = async () => {
			hydrateSlide(slides[target]);
			if (multi) {
				hydrateSlide(slides[(target + 1) % slides.length]);
			}

			const ok = await waitDecoded(slides[target]);

			if (token !== requestToken) {
				return;
			}

			if (!ok) {
				// Keep the healthy displayed slide; unlock by resetting request to it.
				requestedIndex = index;
				restartTimerIfNeeded(restart);
				return;
			}

			index = target;
			requestedIndex = target;
			applyActiveClasses();
			restartTimerIfNeeded(restart);
		};

		run();
	};

	const tick = () => {
		if (paused || reduceMotion || !multi) {
			return;
		}
		activate(requestedIndex + 1);
	};

	const startTimer = () => {
		if (!multi || reduceMotion || paused) {
			return;
		}
		clearTimer();
		startedAt = Date.now();
		const duration = Math.max(16, remaining);
		startProgressAnimation(duration);
		timerId = window.setTimeout(tick, duration);
	};

	const pause = () => {
		if (paused || !multi || reduceMotion) {
			return;
		}
		paused = true;
		remaining = Math.max(0, remaining - (Date.now() - startedAt));
		clearTimer();
		if (progress) {
			const computed = window.getComputedStyle(progress).transform;
			progress.classList.remove('is-running');
			progress.style.animationDuration = '';
			if (computed && computed !== 'none') {
				progress.style.transform = computed;
			}
		}
		root.classList.add('is-paused');
	};

	const resume = () => {
		if (!paused || !multi || reduceMotion || pointerActive) {
			return;
		}
		// Do not resume autoplay while a destination is still loading.
		if (requestedIndex !== index) {
			paused = false;
			root.classList.remove('is-paused');
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
			activate(requestedIndex - 1);
		});
	});

	nextButtons.forEach((btn) => {
		btn.addEventListener('click', (event) => {
			event.preventDefault();
			event.stopPropagation();
			activate(requestedIndex + 1);
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
			activate(requestedIndex + 1);
		} else if (event.key === 'ArrowRight') {
			event.preventDefault();
			activate(requestedIndex - 1);
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
				activate(requestedIndex + 1);
			} else {
				activate(requestedIndex - 1);
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

	// Slide 0 is already fully sourced in HTML — mark hydrated.
	if (slides[0]) {
		slides[0].dataset.ghahghahHeroHydrated = '1';
	}

	applyActiveClasses();
	if (multi) {
		hydrateSlide(slides[1 % slides.length]);
	}

	// Enable crossfade only after first slide can paint for LCP (no opacity transition yet).
	const enableMotion = () => {
		root.classList.add('is-ready');
		if (multi && !reduceMotion) {
			remaining = intervalMs;
			setProgress(0);
			startTimer();
		}
	};

	const boot = async () => {
		const img = slides[0]?.querySelector('img');
		if (img) {
			try {
				if (img.decode) {
					await img.decode();
				}
			} catch (err) {
				/* ignore decode errors on boot — slide 0 already painted from HTML */
			}
		}
		// Two frames + short settle so LCP can commit while transitions are still off.
		await new Promise((resolve) => {
			window.requestAnimationFrame(() => {
				window.requestAnimationFrame(() => {
					window.setTimeout(resolve, 120);
				});
			});
		});
		enableMotion();
	};

	boot();
})();
