/**
 * Featured products carousel — scroll-snap + side controls.
 * Touch = native overflow. Mouse drag = same “content follows pointer” feel.
 */
(() => {
	'use strict';

	const root = document.querySelector('[data-ghahghah-featured]');
	if (!root) {
		return;
	}

	const track = root.querySelector('[data-ghahghah-featured-track]');
	const controls = root.querySelector('[data-ghahghah-featured-controls]');
	const prevBtn = root.querySelector('[data-ghahghah-featured-prev]');
	const nextBtn = root.querySelector('[data-ghahghah-featured-next]');
	const live = root.querySelector('[data-ghahghah-featured-live]');
	if (!track) {
		return;
	}

	const items = () => Array.from(track.querySelectorAll('[data-ghahghah-featured-item]'));
	const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const behavior = () => (reduceMotion() ? 'auto' : 'smooth');
	const canScroll = () => track.scrollWidth > track.clientWidth + 2;

	const closestIndex = () => {
		const list = items();
		if (!list.length) {
			return 0;
		}
		const startX = track.getBoundingClientRect().right;
		let best = 0;
		let bestDist = Infinity;
		list.forEach((item, index) => {
			const dist = Math.abs(item.getBoundingClientRect().right - startX);
			if (dist < bestDist) {
				bestDist = dist;
				best = index;
			}
		});
		return best;
	};

	const atEnd = () => {
		const list = items();
		if (!list.length) {
			return true;
		}
		return list[list.length - 1].getBoundingClientRect().left >= track.getBoundingClientRect().left - 3;
	};

	const updateControls = () => {
		if (!controls || !prevBtn || !nextBtn) {
			return;
		}
		const scrollable = canScroll();
		const atBeginning = closestIndex() <= 0;
		const atLast = closestIndex() >= items().length - 1 || atEnd();

		const hidePrev = !scrollable || atBeginning;
		const hideNext = !scrollable || atLast;

		prevBtn.disabled = hidePrev;
		nextBtn.disabled = hideNext;
		prevBtn.hidden = hidePrev;
		nextBtn.hidden = hideNext;

		/* Hide the whole control strip only when neither button is needed. */
		controls.hidden = hidePrev && hideNext;
	};

	const announce = () => {
		if (!live) {
			return;
		}
		const list = items();
		const total = list.length;
		if (!total) {
			return;
		}
		const index = Math.min(total, Math.max(1, closestIndex() + 1));
		const current = list[index - 1];
		live.textContent = current?.hasAttribute('data-ghahghah-featured-all')
			? `کارت مشاهده همه محصولات، ${index} از ${total}`
			: `محصول ${index} از ${total}`;
	};

	const goTo = (index) => {
		const list = items();
		if (!list.length) {
			return;
		}
		const clamped = Math.max(0, Math.min(list.length - 1, index));
		list[clamped].scrollIntoView({
			inline: 'start',
			block: 'nearest',
			behavior: behavior(),
		});
		window.setTimeout(() => {
			updateControls();
			announce();
		}, reduceMotion() ? 0 : 320);
	};

	const move = (delta) => goTo(closestIndex() + delta);

	prevBtn?.addEventListener('click', () => move(-1));
	nextBtn?.addEventListener('click', () => move(1));

	let scrollTimer = 0;
	track.addEventListener(
		'scroll',
		() => {
			window.requestAnimationFrame(updateControls);
			window.clearTimeout(scrollTimer);
			scrollTimer = window.setTimeout(announce, 120);
		},
		{ passive: true }
	);

	track.addEventListener('keydown', (event) => {
		if (event.key === 'ArrowLeft') {
			event.preventDefault();
			move(1);
		} else if (event.key === 'ArrowRight') {
			event.preventDefault();
			move(-1);
		} else if (event.key === 'Home') {
			event.preventDefault();
			goTo(0);
		} else if (event.key === 'End') {
			event.preventDefault();
			goTo(items().length - 1);
		}
	});

	track.addEventListener('focusin', (event) => {
		const item = event.target.closest('[data-ghahghah-featured-item]');
		if (!item || !canScroll()) {
			return;
		}
		item.scrollIntoView({
			inline: 'nearest',
			block: 'nearest',
			behavior: behavior(),
		});
	});

	/**
	 * Mouse only — touch keeps native scrolling (already correct).
	 *
	 * Native touch = content follows the finger.
	 * Chromium RTL uses negative scrollLeft toward the end, and content
	 * moves with the pointer when: scrollTo(originScroll - deltaX).
	 */
	let dragging = false;
	let pointerId = null;
	let lastX = 0;
	let didDrag = false;

	const clearSelection = () => {
		window.getSelection?.()?.removeAllRanges?.();
	};

	const onPointerMove = (event) => {
		if (!dragging || event.pointerId !== pointerId) {
			return;
		}
		const dx = event.clientX - lastX;
		lastX = event.clientX;
		if (dx === 0) {
			return;
		}
		if (Math.abs(dx) > 1) {
			didDrag = true;
		}
		clearSelection();
		event.preventDefault();
		/*
		 * Incremental “grab”: move content with the pointer.
		 * scrollBy(-dx) matches native RTL touch (content follows finger).
		 * Snap is off while .is-dragging is set.
		 */
		track.scrollBy({ left: -dx, behavior: 'auto' });
	};

	const onPointerUp = (event) => {
		if (!dragging || event.pointerId !== pointerId) {
			return;
		}
		dragging = false;
		pointerId = null;
		track.classList.remove('is-dragging');
		document.removeEventListener('pointermove', onPointerMove, true);
		document.removeEventListener('pointerup', onPointerUp, true);
		document.removeEventListener('pointercancel', onPointerUp, true);
	};

	track.addEventListener('pointerdown', (event) => {
		if (event.pointerType !== 'mouse' || event.button !== 0 || !canScroll()) {
			return;
		}
		clearSelection();
		dragging = true;
		pointerId = event.pointerId;
		lastX = event.clientX;
		didDrag = false;
		track.classList.add('is-dragging');
		document.addEventListener('pointermove', onPointerMove, true);
		document.addEventListener('pointerup', onPointerUp, true);
		document.addEventListener('pointercancel', onPointerUp, true);
	});

	track.addEventListener(
		'click',
		(event) => {
			if (!didDrag) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();
			didDrag = false;
		},
		true
	);

	track.addEventListener('dragstart', (event) => {
		event.preventDefault();
	});

	window.addEventListener('resize', updateControls);
	updateControls();
})();
