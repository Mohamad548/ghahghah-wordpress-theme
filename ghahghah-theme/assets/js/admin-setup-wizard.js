(function () {
	'use strict';

	var cfg = window.ghahghahSetupWizard || {};
	var root = document.querySelector('.ghahghah-wizard');
	if (!root || !cfg.ajaxUrl) {
		return;
	}

	var order = ['welcome', 'core', 'content', 'menus', 'done'];
	var idx = 0;
	var nextBtn = root.querySelector('[data-ghahghah-wizard-next]');
	var homeBtn = root.querySelector('[data-ghahghah-wizard-home]');
	var logEl = root.querySelector('[data-ghahghah-wizard-log]');
	var busy = false;

	function setStep(i) {
		idx = Math.max(0, Math.min(i, order.length - 1));
		var key = order[idx];
		root.querySelectorAll('[data-step]').forEach(function (el, n) {
			el.classList.toggle('is-current', n === idx);
			el.classList.toggle('is-done', n < idx);
		});
		root.querySelectorAll('[data-pane]').forEach(function (pane) {
			pane.classList.toggle('is-active', pane.getAttribute('data-pane') === key);
		});
		if (nextBtn) {
			if (key === 'done') {
				nextBtn.hidden = true;
			} else if (key === 'welcome') {
				nextBtn.hidden = false;
				nextBtn.textContent = cfg.i18n && cfg.i18n.next ? 'شروع نصب' : 'شروع نصب';
				nextBtn.disabled = false;
			} else {
				nextBtn.hidden = false;
				nextBtn.textContent = (cfg.i18n && cfg.i18n.next) || 'ادامه';
				nextBtn.disabled = false;
			}
		}
		if (homeBtn) {
			homeBtn.hidden = key !== 'done';
		}
	}

	function showLog(text, isError) {
		if (!logEl) {
			return;
		}
		logEl.hidden = !text;
		logEl.textContent = text || '';
		logEl.style.borderColor = isError ? '#d71920' : '#e8e2d6';
	}

	function run(step) {
		if (busy) {
			return;
		}
		busy = true;
		if (nextBtn) {
			nextBtn.disabled = true;
			nextBtn.textContent = (cfg.i18n && cfg.i18n.running) || '…';
		}
		showLog((cfg.i18n && cfg.i18n.running) || '…', false);

		var body = new FormData();
		body.append('action', 'ghahghah_setup_wizard_step');
		body.append('nonce', cfg.nonce || '');
		body.append('step', step);

		fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (r) {
				return r.json();
			})
			.then(function (json) {
				busy = false;
				if (!json || !json.success) {
					showLog(
						(json && json.data && json.data.message) ||
							(cfg.i18n && cfg.i18n.failed) ||
							'error',
						true
					);
					if (nextBtn) {
						nextBtn.disabled = false;
						nextBtn.textContent = (cfg.i18n && cfg.i18n.next) || 'ادامه';
					}
					return;
				}
				var data = json.data || {};
				showLog(data.message || '', !data.ok);
				var next = data.next || order[Math.min(idx + 1, order.length - 1)];
				var nextIndex = order.indexOf(next);
				if (nextIndex < 0) {
					nextIndex = Math.min(idx + 1, order.length - 1);
				}
				setStep(nextIndex);
				if (order[nextIndex] === 'done') {
					// Mark complete on server.
					var doneBody = new FormData();
					doneBody.append('action', 'ghahghah_setup_wizard_step');
					doneBody.append('nonce', cfg.nonce || '');
					doneBody.append('step', 'done');
					fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: doneBody });
				}
			})
			.catch(function () {
				busy = false;
				showLog((cfg.i18n && cfg.i18n.failed) || 'error', true);
				if (nextBtn) {
					nextBtn.disabled = false;
					nextBtn.textContent = (cfg.i18n && cfg.i18n.next) || 'ادامه';
				}
			});
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', function () {
			var step = order[idx];
			if (step === 'done') {
				return;
			}
			run(step);
		});
	}

	setStep(0);
})();
