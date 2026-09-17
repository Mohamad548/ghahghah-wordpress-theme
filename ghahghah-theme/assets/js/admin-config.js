/**
 * Theme config tabs (nested groups) + media uploader fields.
 */
(() => {
	'use strict';

	const root = document.querySelector('[data-ghahghah-config]');
	if (!root) {
		return;
	}

	const config = window.ghahghahAdminConfig || {};
	const tabParam = config.tabParam || 'tab';
	const defaultTab = config.defaultTab || 'header';
	const validTabs = Array.isArray(config.validTabs) ? config.validTabs : [];
	const links = Array.from(root.querySelectorAll('[data-ghahghah-config-tab]'));
	const panels = Array.from(root.querySelectorAll('[data-ghahghah-config-panel]'));
	const content = document.getElementById('ghahghah-config-content');

	const isValidTab = (tab) => validTabs.includes(tab);

	const syncGroupOpenState = (activeTab) => {
		root.querySelectorAll('[data-ghahghah-nav-group]').forEach((group) => {
			const childLinks = group.querySelectorAll('[data-ghahghah-config-tab]');
			const childIds = Array.from(childLinks).map((el) => el.getAttribute('data-ghahghah-config-tab'));
			const shouldOpen = childIds.includes(activeTab);
			const toggle = group.querySelector('[data-ghahghah-nav-group-toggle]');
			const subnav = group.querySelector('.ghahghah-config__subnav');
			group.classList.toggle('is-open', shouldOpen);
			if (toggle) {
				toggle.classList.toggle('is-active', shouldOpen);
				toggle.setAttribute('aria-expanded', String(shouldOpen));
			}
			if (subnav) {
				subnav.hidden = !shouldOpen;
			}
		});
	};

	const readTabFromUrl = () => {
		const params = new URLSearchParams(window.location.search);
		const tab = params.get(tabParam) || defaultTab;
		return isValidTab(tab) ? tab : defaultTab;
	};

	const writeTabToUrl = (tab, { replace = false } = {}) => {
		const url = new URL(window.location.href);
		url.searchParams.set(tabParam, tab);
		url.searchParams.delete('ghahghah_saved');
		const method = replace ? 'replaceState' : 'pushState';
		window.history[method]({ ghahghahTab: tab }, '', url);
	};

	const activateTab = (tab, { updateHistory = true, replace = false, focusContent = false } = {}) => {
		const next = isValidTab(tab) ? tab : defaultTab;
		root.dataset.activeTab = next;

		links.forEach((link) => {
			const active = link.getAttribute('data-ghahghah-config-tab') === next;
			link.classList.toggle('is-active', active);
			link.setAttribute('aria-current', active ? 'page' : 'false');
		});

		panels.forEach((panel) => {
			const active = panel.getAttribute('data-ghahghah-config-panel') === next;
			panel.classList.toggle('is-active', active);
			panel.hidden = !active;
		});

		syncGroupOpenState(next);

		if (updateHistory) {
			writeTabToUrl(next, { replace });
		}

		if (focusContent && content) {
			content.focus({ preventScroll: true });
		}
	};

	const toast = root.querySelector('[data-ghahghah-config-toast]');
	const toastClose = root.querySelector('[data-ghahghah-toast-close]');
	const clearSavedFlag = () => {
		const url = new URL(window.location.href);
		if (!url.searchParams.has('ghahghah_saved')) {
			return;
		}
		url.searchParams.delete('ghahghah_saved');
		window.history.replaceState({}, '', url);
	};

	const dismissToast = () => {
		if (!toast || !toast.isConnected) {
			clearSavedFlag();
			return;
		}
		toast.classList.add('is-leaving');
		window.setTimeout(() => {
			if (toast.isConnected) {
				toast.remove();
			}
			clearSavedFlag();
		}, 280);
	};

	activateTab(readTabFromUrl(), { replace: true, updateHistory: !toast });

	if (toast) {
		if (toastClose) {
			toastClose.addEventListener('click', dismissToast);
		}
		window.setTimeout(dismissToast, 4200);
	}

	links.forEach((link) => {
		link.addEventListener('click', (event) => {
			const tab = link.getAttribute('data-ghahghah-config-tab');
			if (!tab || !isValidTab(tab)) {
				return;
			}
			event.preventDefault();
			if (root.dataset.activeTab === tab) {
				return;
			}
			dismissToast();
			activateTab(tab, { focusContent: true });
		});
	});

	root.querySelectorAll('[data-ghahghah-nav-group-toggle]').forEach((toggle) => {
		toggle.addEventListener('click', () => {
			const group = toggle.closest('[data-ghahghah-nav-group]');
			if (!group) {
				return;
			}
			const willOpen = !group.classList.contains('is-open');
			const subnav = group.querySelector('.ghahghah-config__subnav');
			group.classList.toggle('is-open', willOpen);
			toggle.classList.toggle('is-active', willOpen);
			toggle.setAttribute('aria-expanded', String(willOpen));
			if (subnav) {
				subnav.hidden = !willOpen;
			}

			if (willOpen) {
				const firstChild = group.querySelector('[data-ghahghah-config-tab]');
				const tab = firstChild ? firstChild.getAttribute('data-ghahghah-config-tab') : null;
				if (tab && isValidTab(tab) && root.dataset.activeTab !== tab) {
					dismissToast();
					activateTab(tab, { focusContent: true });
				}
			}
		});
	});

	window.addEventListener('popstate', () => {
		activateTab(readTabFromUrl(), { updateHistory: false });
	});

	const bindMediaField = (field) => {
		const input = field.querySelector('[data-ghahghah-media-input]');
		const preview = field.querySelector('[data-ghahghah-media-preview]');
		const uploadBtn = field.querySelector('[data-ghahghah-media-upload]');
		const resetBtn = field.querySelector('[data-ghahghah-media-reset]');
		const previewWrap = field.querySelector('.ghahghah-media-field__preview');
		const emptyNote = field.querySelector('.ghahghah-media-field__empty');
		if (!input || !preview || !uploadBtn || typeof wp === 'undefined' || !wp.media) {
			return;
		}

		const syncPreviewState = (src) => {
			const hasSrc = Boolean(src);
			preview.hidden = !hasSrc;
			preview.src = src || '';
			if (previewWrap) {
				previewWrap.classList.toggle('is-empty', !hasSrc);
			}
			if (emptyNote) {
				emptyNote.hidden = hasSrc;
			}
		};

		let frame = null;

		uploadBtn.addEventListener('click', (event) => {
			event.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: config.mediaTitle || 'Select image',
				button: { text: config.mediaButton || 'Use image' },
				library: { type: 'image' },
				multiple: false,
			});

			frame.on('select', () => {
				const attachment = frame.state().get('selection').first().toJSON();
				input.value = String(attachment.id || '');
				syncPreviewState(attachment.url || preview.dataset.defaultSrc || '');
				if (resetBtn) {
					resetBtn.hidden = false;
				}
			});

			frame.open();
		});

		if (resetBtn) {
			resetBtn.addEventListener('click', (event) => {
				event.preventDefault();
				input.value = '0';
				syncPreviewState(preview.dataset.defaultSrc || '');
				resetBtn.hidden = true;
			});
		}
	};

	root.querySelectorAll('[data-ghahghah-media-field]').forEach(bindMediaField);

	const sliderAdmin = root.querySelector('[data-ghahghah-slider-admin]');
	if (sliderAdmin) {
		const list = sliderAdmin.querySelector('[data-ghahghah-slider-list]');
		const template = sliderAdmin.querySelector('[data-ghahghah-slider-template]');
		const addBtn = sliderAdmin.querySelector('[data-ghahghah-slider-add]');
		const maxSlides = 12;

		const pad = (n) => String(n).padStart(2, '0');

		const refreshBadges = () => {
			if (!list) {
				return;
			}
			Array.from(list.querySelectorAll('[data-ghahghah-slider-row]')).forEach((row, index) => {
				const badge = row.querySelector('[data-ghahghah-slider-badge]');
				if (badge) {
					badge.textContent = pad(index + 1);
				}
			});
		};

		const rowCount = () => (list ? list.querySelectorAll('[data-ghahghah-slider-row]').length : 0);

		const clearRowFields = (row) => {
			row.querySelectorAll('input[type="text"], input[type="url"], input[type="hidden"], textarea').forEach((field) => {
				if (field.matches('[data-ghahghah-media-input]')) {
					field.value = '0';
				} else {
					field.value = '';
				}
			});
			const preview = row.querySelector('[data-ghahghah-media-preview]');
			const previewWrap = row.querySelector('.ghahghah-media-field__preview');
			const emptyNote = row.querySelector('.ghahghah-media-field__empty');
			const resetBtn = row.querySelector('[data-ghahghah-media-reset]');
			if (preview) {
				preview.hidden = true;
				preview.src = '';
			}
			if (previewWrap) {
				previewWrap.classList.add('is-empty');
			}
			if (emptyNote) {
				emptyNote.hidden = false;
			}
			if (resetBtn) {
				resetBtn.hidden = true;
			}
		};

		if (addBtn && list && template) {
			addBtn.addEventListener('click', () => {
				if (rowCount() >= maxSlides) {
					return;
				}
				const node = template.content.cloneNode(true);
				list.appendChild(node);
				const added = list.lastElementChild;
				if (added) {
					added.querySelectorAll('[data-ghahghah-media-field]').forEach(bindMediaField);
				}
				refreshBadges();
			});
		}

		if (list) {
			list.addEventListener('click', (event) => {
				const target = event.target;
				if (!(target instanceof Element)) {
					return;
				}
				const row = target.closest('[data-ghahghah-slider-row]');
				if (!row || !list.contains(row)) {
					return;
				}

				if (target.closest('[data-ghahghah-slider-remove]')) {
					if (rowCount() <= 1) {
						clearRowFields(row);
						refreshBadges();
						return;
					}
					row.remove();
					refreshBadges();
					return;
				}

				if (target.closest('[data-ghahghah-slider-up]')) {
					const prev = row.previousElementSibling;
					if (prev) {
						list.insertBefore(row, prev);
						refreshBadges();
					}
					return;
				}

				if (target.closest('[data-ghahghah-slider-down]')) {
					const next = row.nextElementSibling;
					if (next) {
						list.insertBefore(next, row);
						refreshBadges();
					}
				}
			});
		}

		refreshBadges();
	}

	const socialAdmin = root.querySelector('[data-ghahghah-social-admin]');
	if (socialAdmin) {
		const list = socialAdmin.querySelector('[data-ghahghah-social-list]');
		const template = socialAdmin.querySelector('[data-ghahghah-social-template]');
		const addBtn = socialAdmin.querySelector('[data-ghahghah-social-add]');
		const maxItems = 8;

		const pad = (n) => String(n).padStart(2, '0');

		const refreshBadges = () => {
			if (!list) {
				return;
			}
			Array.from(list.querySelectorAll('[data-ghahghah-social-row]')).forEach((row, index) => {
				const badge = row.querySelector('[data-ghahghah-social-badge]');
				if (badge) {
					badge.textContent = pad(index + 1);
				}
			});
		};

		const rowCount = () => (list ? list.querySelectorAll('[data-ghahghah-social-row]').length : 0);

		const clearRowFields = (row) => {
			row.querySelectorAll('input[type="text"], input[type="url"], input[type="hidden"], select').forEach((field) => {
				if (field.matches('[data-ghahghah-media-input]')) {
					field.value = '0';
				} else if (field.tagName === 'SELECT') {
					field.value = 'custom';
				} else {
					field.value = '';
				}
			});
			const preview = row.querySelector('[data-ghahghah-media-preview]');
			const previewWrap = row.querySelector('.ghahghah-media-field__preview');
			const emptyNote = row.querySelector('.ghahghah-media-field__empty');
			const resetBtn = row.querySelector('[data-ghahghah-media-reset]');
			if (preview) {
				preview.hidden = true;
				preview.src = '';
			}
			if (previewWrap) {
				previewWrap.classList.add('is-empty');
			}
			if (emptyNote) {
				emptyNote.hidden = false;
			}
			if (resetBtn) {
				resetBtn.hidden = true;
			}
		};

		if (addBtn && list && template) {
			addBtn.addEventListener('click', () => {
				if (rowCount() >= maxItems) {
					return;
				}
				const node = template.content.cloneNode(true);
				list.appendChild(node);
				const added = list.lastElementChild;
				if (added) {
					added.querySelectorAll('[data-ghahghah-media-field]').forEach(bindMediaField);
				}
				refreshBadges();
			});
		}

		if (list) {
			list.addEventListener('click', (event) => {
				const target = event.target;
				if (!(target instanceof Element)) {
					return;
				}
				const row = target.closest('[data-ghahghah-social-row]');
				if (!row || !list.contains(row)) {
					return;
				}

				if (target.closest('[data-ghahghah-social-remove]')) {
					if (rowCount() <= 1) {
						clearRowFields(row);
						refreshBadges();
						return;
					}
					row.remove();
					refreshBadges();
					return;
				}

				if (target.closest('[data-ghahghah-social-up]')) {
					const prev = row.previousElementSibling;
					if (prev) {
						list.insertBefore(row, prev);
						refreshBadges();
					}
					return;
				}

				if (target.closest('[data-ghahghah-social-down]')) {
					const next = row.nextElementSibling;
					if (next) {
						list.insertBefore(next, row);
						refreshBadges();
					}
				}
			});
		}

		refreshBadges();
	}

	const stepsAdmin = root.querySelector('[data-ghahghah-steps-admin]');
	if (stepsAdmin) {
		const list = stepsAdmin.querySelector('[data-ghahghah-steps-list]');
		const template = stepsAdmin.querySelector('[data-ghahghah-steps-template]');
		const addBtn = stepsAdmin.querySelector('[data-ghahghah-steps-add]');
		const maxSteps = 12;

		const pad = (n) => String(n).padStart(2, '0');

		const refreshBadges = () => {
			if (!list) {
				return;
			}
			Array.from(list.querySelectorAll('[data-ghahghah-steps-row]')).forEach((row, index) => {
				const badge = row.querySelector('[data-ghahghah-steps-badge]');
				if (badge) {
					badge.textContent = pad(index + 1);
				}
			});
		};

		const rowCount = () => (list ? list.querySelectorAll('[data-ghahghah-steps-row]').length : 0);

		if (addBtn && list && template) {
			addBtn.addEventListener('click', () => {
				if (rowCount() >= maxSteps) {
					return;
				}
				const node = template.content.cloneNode(true);
				list.appendChild(node);
				refreshBadges();
			});
		}

		if (list) {
			list.addEventListener('click', (event) => {
				const target = event.target;
				if (!(target instanceof Element)) {
					return;
				}
				const row = target.closest('[data-ghahghah-steps-row]');
				if (!row || !list.contains(row)) {
					return;
				}

				if (target.closest('[data-ghahghah-steps-remove]')) {
					if (rowCount() <= 1) {
						row.querySelectorAll('input, textarea').forEach((field) => {
							field.value = '';
						});
						refreshBadges();
						return;
					}
					row.remove();
					refreshBadges();
					return;
				}

				if (target.closest('[data-ghahghah-steps-up]')) {
					const prev = row.previousElementSibling;
					if (prev) {
						list.insertBefore(row, prev);
						refreshBadges();
					}
					return;
				}

				if (target.closest('[data-ghahghah-steps-down]')) {
					const next = row.nextElementSibling;
					if (next) {
						list.insertBefore(next, row);
						refreshBadges();
					}
				}
			});
		}

		refreshBadges();
	}
})();
