/**
 * Articles carousel — scroll-snap + side controls (same behavior as featured).
 */
(() => {
	'use strict';

	const root = document.querySelector('[data-ghahghah-articles]');
	if (!root) {
		return;
	}

	const track = root.querySelector('[data-ghahghah-articles-track]');
	const controls = root.querySelector('[data-ghahghah-articles-controls]');
	const prevBtn = root.querySelector('[data-ghahghah-articles-prev]');
	const nextBtn = root.querySelector('[data-ghahghah-articles-next]');
	const live = root.querySelector('[data-ghahghah-articles-live]');
	if (!track) {
		return;
	}

	const items = () => Array.from(track.querySelectorAll('[data-ghahghah-articles-item]'));
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
		const index = closestIndex() + 1;
		live.textContent = `${index} از ${total}`;
	};

	const goTo = (index) => {
		const list = items();
		if (!list.length) {
			return;
		}
		const clamped = Math.max(0, Math.min(index, list.length - 1));
		list[clamped].scrollIntoView({
			inline: 'start',
			block: 'nearest',
			behavior: behavior(),
		});
		window.requestAnimationFrame(() => {
			updateControls();
			announce();
		});
	};

	const move = (delta) => {
		goTo(closestIndex() + delta);
	};

	if (prevBtn) {
		prevBtn.addEventListener('click', () => move(-1));
	}
	if (nextBtn) {
		nextBtn.addEventListener('click', () => move(1));
	}

	track.addEventListener('scroll', () => {
		window.requestAnimationFrame(updateControls);
	}, { passive: true });

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
		const item = event.target.closest('[data-ghahghah-articles-item]');
		if (!item || !canScroll()) {
			return;
		}
		item.scrollIntoView({
			inline: 'nearest',
			block: 'nearest',
			behavior: behavior(),
		});
	});

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
