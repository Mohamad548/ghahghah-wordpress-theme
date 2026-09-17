/**
 * Header interactions: mobile drawer + desktop hover / mobile accordion submenus.
 */
(() => {
	'use strict';

	const header = document.querySelector('[data-ghahghah-header]');
	if (!header) {
		return;
	}

	const openBtn = header.querySelector('[data-ghahghah-drawer-open]');
	const closeBtn = header.querySelector('[data-ghahghah-drawer-close]');
	const drawer = header.querySelector('[data-ghahghah-drawer]');
	const overlay = header.querySelector('[data-ghahghah-drawer-overlay]');
	const mq = window.matchMedia('(min-width: 64rem)');
	let lastFocus = null;
	let scrollY = 0;

	const getFocusable = (root) =>
		Array.from(
			root.querySelectorAll(
				'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
			)
		).filter((el) => !el.hasAttribute('disabled') && el.getAttribute('aria-hidden') !== 'true');

	const setSubmenuOpen = (item, open) => {
		const toggle = item.querySelector(':scope > .site-nav__row [data-ghahghah-submenu-toggle]');
		const panelId = toggle ? toggle.getAttribute('aria-controls') : null;
		const panel = panelId ? document.getElementById(panelId) : item.querySelector(':scope > .site-nav__sub');
		if (!toggle || !panel) {
			return;
		}
		toggle.setAttribute('aria-expanded', String(open));
		panel.hidden = !open;
		item.classList.toggle('is-open', open);
	};

	const closeAllSubmenus = (scope = header) => {
		scope.querySelectorAll('.site-nav__item--has-children.is-open, .site-nav__item--has-children').forEach((item) => {
			const toggle = item.querySelector(':scope > .site-nav__row [data-ghahghah-submenu-toggle][aria-expanded="true"]');
			if (toggle || item.classList.contains('is-open')) {
				setSubmenuOpen(item, false);
			}
		});
	};

	const setDrawerOpen = (open) => {
		if (!drawer || !overlay || !openBtn) {
			return;
		}

		if (open) {
			lastFocus = document.activeElement;
			scrollY = window.scrollY || window.pageYOffset;
			drawer.hidden = false;
			overlay.hidden = false;
			openBtn.setAttribute('aria-expanded', 'true');
			document.body.classList.add('ghahghah-drawer-open');
			document.body.style.top = `-${scrollY}px`;
			document.body.style.position = 'fixed';
			document.body.style.width = '100%';
			const focusables = getFocusable(drawer);
			(focusables[0] || closeBtn || drawer).focus();
			return;
		}

		drawer.hidden = true;
		overlay.hidden = true;
		openBtn.setAttribute('aria-expanded', 'false');
		document.body.classList.remove('ghahghah-drawer-open');
		document.body.style.position = '';
		document.body.style.top = '';
		document.body.style.width = '';
		window.scrollTo(0, scrollY);
		closeAllSubmenus(drawer);

		if (lastFocus && typeof lastFocus.focus === 'function') {
			lastFocus.focus();
		} else {
			openBtn.focus();
		}
	};

	const onDesktopChange = () => {
		if (mq.matches) {
			setDrawerOpen(false);
			setMobileSearchOpen(false);
			closeAllSubmenus(header);
		}
	};

	if (openBtn) {
		openBtn.addEventListener('click', () => setDrawerOpen(true));
	}
	if (closeBtn) {
		closeBtn.addEventListener('click', () => setDrawerOpen(false));
	}
	if (overlay) {
		overlay.addEventListener('click', () => setDrawerOpen(false));
	}

	const mobileSearch = header.querySelector('[data-ghahghah-mobile-search]');
	const mobileSearchOpen = header.querySelector('[data-ghahghah-mobile-search-open]');

	const setMobileSearchOpen = (open) => {
		if (!mobileSearch || !mobileSearchOpen) {
			return;
		}
		mobileSearch.hidden = !open;
		mobileSearchOpen.setAttribute('aria-expanded', String(open));
		header.classList.toggle('is-mobile-search-open', open);
		if (open) {
			const input = mobileSearch.querySelector('[data-ghahghah-live-search-input]');
			if (input) {
				window.setTimeout(() => input.focus(), 30);
			}
		}
	};

	if (mobileSearchOpen) {
		mobileSearchOpen.addEventListener('click', (event) => {
			event.stopPropagation();
			const next = mobileSearchOpen.getAttribute('aria-expanded') !== 'true';
			setMobileSearchOpen(next);
		});
	}

	if (openBtn) {
		openBtn.addEventListener('click', () => setMobileSearchOpen(false));
	}

	document.addEventListener(
		'pointerdown',
		(event) => {
			if (!mobileSearch || mobileSearch.hidden || mq.matches) {
				return;
			}
			const target = event.target;
			if (!(target instanceof Node)) {
				return;
			}
			if (mobileSearch.contains(target) || (mobileSearchOpen && mobileSearchOpen.contains(target))) {
				return;
			}
			setMobileSearchOpen(false);
		},
		true
	);

	// Desktop: open submenu on hover; delay close so pointer can reach the panel.
	header.querySelectorAll('.site-nav--desktop .site-nav__item--has-children').forEach((item) => {
		let closeTimer = null;

		const cancelClose = () => {
			if (closeTimer) {
				window.clearTimeout(closeTimer);
				closeTimer = null;
			}
		};

		item.addEventListener('mouseenter', () => {
			if (!mq.matches) {
				return;
			}
			cancelClose();
			const list = item.closest('.site-nav__list');
			if (list) {
				list.querySelectorAll('.site-nav__item--has-children').forEach((other) => {
					if (other !== item) {
						setSubmenuOpen(other, false);
					}
				});
			}
			setSubmenuOpen(item, true);
		});

		item.addEventListener('mouseleave', () => {
			if (!mq.matches) {
				return;
			}
			cancelClose();
			closeTimer = window.setTimeout(() => {
				setSubmenuOpen(item, false);
				closeTimer = null;
			}, 180);
		});

		item.addEventListener('focusin', () => {
			if (!mq.matches) {
				return;
			}
			cancelClose();
			setSubmenuOpen(item, true);
		});

		item.addEventListener('focusout', (event) => {
			if (!mq.matches) {
				return;
			}
			const next = event.relatedTarget;
			if (next && item.contains(next)) {
				return;
			}
			cancelClose();
			closeTimer = window.setTimeout(() => {
				setSubmenuOpen(item, false);
				closeTimer = null;
			}, 120);
		});
	});

	// Mobile accordion via toggle button; desktop toggle also works on click/keyboard.
	header.addEventListener('click', (event) => {
		const toggle = event.target.closest('[data-ghahghah-submenu-toggle]');
		if (!toggle || !header.contains(toggle)) {
			return;
		}

		event.preventDefault();
		const item = toggle.closest('.site-nav__item--has-children');
		if (!item) {
			return;
		}

		const willOpen = toggle.getAttribute('aria-expanded') !== 'true';
		const listRoot = toggle.closest('.site-nav__list');
		if (listRoot) {
			listRoot.querySelectorAll('.site-nav__item--has-children').forEach((other) => {
				if (other !== item) {
					setSubmenuOpen(other, false);
				}
			});
		}
		setSubmenuOpen(item, willOpen);
	});

	document.addEventListener('click', (event) => {
		if (!mq.matches) {
			return;
		}
		if (header.contains(event.target) && event.target.closest('.site-nav__item--has-children')) {
			return;
		}
		closeAllSubmenus(header.querySelector('.site-nav--desktop') || header);
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			if (mobileSearch && !mobileSearch.hidden) {
				setMobileSearchOpen(false);
				if (mobileSearchOpen) {
					mobileSearchOpen.focus();
				}
				return;
			}
			const openItem = header.querySelector('.site-nav__item--has-children.is-open');
			if (openItem && (!drawer || drawer.hidden)) {
				const toggle = openItem.querySelector('[data-ghahghah-submenu-toggle]');
				setSubmenuOpen(openItem, false);
				if (toggle) {
					toggle.focus();
				}
				return;
			}
			if (drawer && !drawer.hidden) {
				setDrawerOpen(false);
			}
			return;
		}

		if (event.key !== 'Tab' || !drawer || drawer.hidden) {
			return;
		}

		const focusables = getFocusable(drawer);
		if (!focusables.length) {
			return;
		}
		const first = focusables[0];
		const last = focusables[focusables.length - 1];
		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	});

	mq.addEventListener('change', onDesktopChange);
	onDesktopChange();

	/* —— Live search —— */
	const liveCfg = window.ghahghahHeader || {};
	const liveUrl = typeof liveCfg.liveSearchUrl === 'string' ? liveCfg.liveSearchUrl : '';
	const liveI18n = liveCfg.i18n || {};

	const escapeHtml = (value) =>
		String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');

	const initLiveSearch = (form) => {
		const input = form.querySelector('[data-ghahghah-live-search-input]');
		const panel = form.querySelector('[data-ghahghah-live-search-results]');
		if (!input || !panel || !liveUrl) {
			return;
		}

		let timer = 0;
		let abortCtrl = null;
		let requestId = 0;

		const closePanel = () => {
			panel.hidden = true;
			panel.innerHTML = '';
			input.setAttribute('aria-expanded', 'false');
			form.classList.remove('is-live-open');
		};

		const openPanel = (html) => {
			panel.innerHTML = html;
			panel.hidden = false;
			input.setAttribute('aria-expanded', 'true');
			form.classList.add('is-live-open');
		};

		const renderItems = (payload) => {
			const items = Array.isArray(payload.items) ? payload.items : [];
			if (!items.length) {
				openPanel(
					`<p class="site-header__search-empty">${escapeHtml(liveI18n.empty || 'نتیجه‌ای یافت نشد')}</p>`
				);
				return;
			}

			const rows = items
				.map((item) => {
					const img = item.image
						? `<img class="site-header__search-thumb" src="${escapeHtml(item.image)}" alt="" width="40" height="40" loading="lazy" decoding="async" />`
						: `<span class="site-header__search-thumb site-header__search-thumb--empty" aria-hidden="true"></span>`;
					const excerpt = item.excerpt
						? `<span class="site-header__search-excerpt">${escapeHtml(item.excerpt)}</span>`
						: '';
					return `<a class="site-header__search-hit" href="${escapeHtml(item.url)}">
						${img}
						<span class="site-header__search-hit-copy">
							<span class="site-header__search-hit-type">${escapeHtml(item.label || '')}</span>
							<span class="site-header__search-hit-title">${escapeHtml(item.title || '')}</span>
							${excerpt}
						</span>
					</a>`;
				})
				.join('');

			const more =
				payload.moreUrl && payload.total > items.length
					? `<a class="site-header__search-more" href="${escapeHtml(payload.moreUrl)}">${escapeHtml(
							liveI18n.more || 'مشاهده همه نتایج'
					  )}</a>`
					: '';

			openPanel(`<div class="site-header__search-list" role="listbox">${rows}</div>${more}`);
		};

		const runSearch = (query) => {
			const q = query.trim();
			if (q.length < 2) {
				closePanel();
				return;
			}

			if (abortCtrl) {
				abortCtrl.abort();
			}
			abortCtrl = new AbortController();
			const currentId = ++requestId;

			openPanel(`<p class="site-header__search-empty">${escapeHtml(liveI18n.loading || 'در حال جستجو…')}</p>`);

			const url = `${liveUrl}?q=${encodeURIComponent(q)}`;
			fetch(url, {
				method: 'GET',
				credentials: 'same-origin',
				signal: abortCtrl.signal,
				headers: { Accept: 'application/json' },
			})
				.then((res) => {
					if (!res.ok) {
						throw new Error('search failed');
					}
					return res.json();
				})
				.then((data) => {
					if (currentId !== requestId) {
						return;
					}
					renderItems(data || {});
				})
				.catch((err) => {
					if (err && err.name === 'AbortError') {
						return;
					}
					if (currentId !== requestId) {
						return;
					}
					openPanel(
						`<p class="site-header__search-empty">${escapeHtml(liveI18n.error || 'خطا در جستجو')}</p>`
					);
				});
		};

		input.addEventListener('input', () => {
			window.clearTimeout(timer);
			timer = window.setTimeout(() => runSearch(input.value || ''), 220);
		});

		input.addEventListener('focus', () => {
			if ((input.value || '').trim().length >= 2 && panel.childElementCount) {
				panel.hidden = false;
				input.setAttribute('aria-expanded', 'true');
				form.classList.add('is-live-open');
			}
		});

		input.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				closePanel();
				input.blur();
			}
		});

		document.addEventListener('click', (event) => {
			if (!form.contains(event.target)) {
				closePanel();
			}
		});
	};

	header.querySelectorAll('[data-ghahghah-live-search]').forEach(initLiveSearch);
})();
