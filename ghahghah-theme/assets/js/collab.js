/**
 * Reveal collab inquiry forms + deep-link via hash.
 */
(() => {
	'use strict';

	const root = document.querySelector('[data-ghahghah-collab]');
	if (!root) {
		return;
	}

	const panels = Array.from(root.querySelectorAll('[data-ghahghah-collab-panel]'));
	const openers = Array.from(root.querySelectorAll('[data-ghahghah-collab-open]'));

	const getPanel = (id) => panels.find((panel) => panel.id === id) || null;

	const closePanel = (panel, { restoreFocusTo = null } = {}) => {
		if (!panel || panel.hidden) {
			return;
		}
		panel.hidden = true;
		openers.forEach((btn) => {
			if (btn.getAttribute('data-ghahghah-collab-open') === panel.id) {
				btn.setAttribute('aria-expanded', 'false');
			}
		});
		if (restoreFocusTo) {
			restoreFocusTo.focus();
		}
	};

	const openPanel = (panel, opener) => {
		if (!panel) {
			return;
		}

		panels.forEach((other) => {
			if (other !== panel) {
				closePanel(other);
			}
		});

		panel.hidden = false;
		if (opener) {
			opener.setAttribute('aria-expanded', 'true');
		} else {
			openers.forEach((btn) => {
				btn.setAttribute(
					'aria-expanded',
					btn.getAttribute('data-ghahghah-collab-open') === panel.id ? 'true' : 'false'
				);
			});
		}

		const title = panel.querySelector('.ghahghah-collab__form-title') || panel.querySelector('.ghahghah-form__title');
		if (title && typeof title.focus === 'function') {
			title.focus({ preventScroll: false });
		}
		panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	};

	const openFromHash = () => {
		const hash = window.location.hash.replace(/^#/, '');
		if (!hash) {
			return;
		}
		const panel = getPanel(hash);
		if (!panel) {
			return;
		}
		const opener = openers.find((el) => el.getAttribute('data-ghahghah-collab-open') === hash) || null;
		openPanel(panel, opener);
	};

	openers.forEach((btn) => {
		btn.addEventListener('click', () => {
			const id = btn.getAttribute('data-ghahghah-collab-open');
			const panel = id ? getPanel(id) : null;
			if (!panel) {
				return;
			}
			if (!panel.hidden && btn.getAttribute('aria-expanded') === 'true') {
				closePanel(panel, { restoreFocusTo: btn });
				return;
			}
			openPanel(panel, btn);
			if (id && history.replaceState) {
				history.replaceState(null, '', `#${id}`);
			}
		});
	});

	root.querySelectorAll('[data-ghahghah-collab-close]').forEach((btn) => {
		btn.addEventListener('click', () => {
			const panel = btn.closest('[data-ghahghah-collab-panel]');
			const id = panel ? panel.id : '';
			const opener = openers.find((el) => el.getAttribute('data-ghahghah-collab-open') === id) || null;
			closePanel(panel, { restoreFocusTo: opener });
			if (history.replaceState && window.location.hash.replace(/^#/, '') === id) {
				history.replaceState(null, '', window.location.pathname + window.location.search);
			}
		});
	});

	document.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape') {
			return;
		}
		const open = panels.find((panel) => !panel.hidden);
		if (!open) {
			return;
		}
		const id = open.id;
		const opener = openers.find((el) => el.getAttribute('data-ghahghah-collab-open') === id) || null;
		closePanel(open, { restoreFocusTo: opener });
	});

	window.addEventListener('hashchange', openFromHash);
	openFromHash();
})();
