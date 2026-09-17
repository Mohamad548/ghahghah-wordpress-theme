/**
 * Inquiry forms — state machine + REST submit.
 */
(() => {
	'use strict';

	const roots = Array.from(document.querySelectorAll('[data-ghahghah-form]'));
	if (!roots.length) {
		return;
	}

	const phoneOk = (value) => {
		const digits = String(value || '').replace(/[^0-9]/g, '');
		if (/^09\d{9}$/.test(digits)) {
			return digits;
		}
		if (/^98\d{10}$/.test(digits)) {
			return `0${digits.slice(2)}`;
		}
		if (/^9\d{9}$/.test(digits)) {
			return `0${digits}`;
		}
		return '';
	};

	const setState = (root, state) => {
		root.setAttribute('data-ghahghah-form-state', state);
		root.querySelectorAll('[data-ghahghah-view]').forEach((view) => {
			const name = view.getAttribute('data-ghahghah-view');
			const show =
				name === state ||
				(state === 'validation' && name === 'idle') ||
				(state === 'idle' && name === 'idle');
			if (name === 'idle') {
				view.hidden = !(state === 'idle' || state === 'validation');
				return;
			}
			view.hidden = name !== state;
		});
	};

	const clearErrors = (root) => {
		root.querySelectorAll('[data-ghahghah-field]').forEach((field) => {
			field.classList.remove('is-invalid');
			const input = field.querySelector('input, select, textarea');
			if (input) {
				input.removeAttribute('aria-invalid');
			}
		});
		root.querySelectorAll('[data-ghahghah-error]').forEach((el) => {
			el.hidden = true;
			const text = el.querySelector('[data-ghahghah-error-text]');
			if (text) {
				text.textContent = '';
			}
		});
	};

	const showFieldError = (root, name, message) => {
		const field = root.querySelector(`[data-ghahghah-field="${name}"]`);
		if (!field) {
			return;
		}
		field.classList.add('is-invalid');
		const input = field.querySelector('input, select, textarea');
		if (input) {
			input.setAttribute('aria-invalid', 'true');
		}
		const err =
			field.querySelector('[data-ghahghah-error]') ||
			root.querySelector(`[data-ghahghah-field="${name}"] + [data-ghahghah-error]`) ||
			(name === 'consent' ? root.querySelector('.ghahghah-form__error--consent') : null);
		if (!err) {
			return;
		}
		err.hidden = false;
		const text = err.querySelector('[data-ghahghah-error-text]');
		if (text) {
			text.textContent = message;
		}
	};

	const collect = (form) => {
		const data = {};
		new FormData(form).forEach((value, key) => {
			data[key] = typeof value === 'string' ? value.trim() : value;
		});
		data.consent = form.querySelector('[name="consent"]')?.checked ? 1 : 0;
		if (form.closest('[data-ghahghah-form="contact"]')) {
			data.consent = 1;
		}
		return data;
	};

	const validate = (root, type, data) => {
		const errors = {};
		if (!data.full_name) {
			errors.full_name = 'نام و نام خانوادگی را وارد کنید.';
		}
		if (!phoneOk(data.phone)) {
			errors.phone = 'شماره تماس را وارد کنید.';
		}

		if (type === 'contact') {
			if (!data.subject) {
				errors.subject = 'موضوع پیام را انتخاب کنید.';
			}
			if (!data.message) {
				errors.message = 'متن پیام را وارد کنید.';
			}
			return errors;
		}

		if (!data.consent) {
			errors.consent =
				type === 'agency'
					? 'برای ادامه باید با بررسی درخواست موافقت کنید.'
					: 'برای ادامه باید با پیگیری درخواست موافقت کنید.';
		}
		if (type === 'wholesale') {
			if (!data.company) {
				errors.company = 'نام مجموعه یا فروشگاه را وارد کنید.';
			}
			if (!data.city) {
				errors.city = 'شهر را انتخاب کنید.';
			}
			if (!data.product) {
				errors.product = 'محصول موردنظر را انتخاب کنید.';
			}
			if (!data.quantity) {
				errors.quantity = 'تعداد تقریبی سفارش را وارد کنید.';
			}
		} else {
			if (!data.province) {
				errors.province = 'استان را انتخاب کنید.';
			}
			if (!data.city) {
				errors.city = 'شهر را انتخاب کنید.';
			}
			if (!data.activity) {
				errors.activity = 'زمینه فعالیت را انتخاب کنید.';
			}
		}
		return errors;
	};

	const bindProvinceCity = (root) => {
		const province = root.querySelector('[data-ghahghah-province]');
		const city = root.querySelector('[data-ghahghah-city]');
		if (!province || !city) {
			return;
		}
		let map = {};
		try {
			map = JSON.parse(root.getAttribute('data-provinces') || '{}');
		} catch (e) {
			map = {};
		}
		province.addEventListener('change', () => {
			const list = map[province.value] || [];
			city.innerHTML = '';
			const placeholder = document.createElement('option');
			placeholder.value = '';
			placeholder.textContent = 'انتخاب شهر';
			city.appendChild(placeholder);
			list.forEach((name) => {
				const opt = document.createElement('option');
				opt.value = name;
				opt.textContent = name;
				city.appendChild(opt);
			});
		});
	};

	const submitForm = async (root) => {
		const type = root.getAttribute('data-ghahghah-form');
		const form = root.querySelector('form[data-ghahghah-view="idle"]');
		const restUrl = root.getAttribute('data-rest-url');
		const nonce = root.getAttribute('data-nonce');
		if (!form || !restUrl || !type) {
			return;
		}

		clearErrors(root);
		const data = collect(form);
		data.type = type;
		data.phone = phoneOk(data.phone) || data.phone;

		const errors = validate(root, type, data);
		if (Object.keys(errors).length) {
			Object.entries(errors).forEach(([key, message]) => showFieldError(root, key, message));
			setState(root, 'validation');
			const firstInvalid = root.querySelector('.is-invalid input, .is-invalid select, .is-invalid textarea');
			firstInvalid?.focus();
			return;
		}

		if (root.dataset.submitting === '1') {
			return;
		}
		root.dataset.submitting = '1';
		setState(root, 'sending');

		let unclear = false;
		try {
			const controller = new AbortController();
			const timer = window.setTimeout(() => {
				unclear = true;
				controller.abort();
			}, 25000);

			const response = await fetch(restUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					Accept: 'application/json',
					'X-Ghahghah-Nonce': nonce || '',
				},
				body: JSON.stringify(data),
				signal: controller.signal,
			});
			window.clearTimeout(timer);

			let payload = null;
			try {
				payload = await response.json();
			} catch (e) {
				payload = null;
			}

			if (response.ok && payload && payload.ok) {
				setState(root, 'success');
				form.reset();
				clearErrors(root);
				return;
			}

			if (response.status === 400 && payload && payload.data && payload.data.fields) {
				Object.entries(payload.data.fields).forEach(([key, message]) => {
					showFieldError(root, key, String(message));
				});
				setState(root, 'validation');
				return;
			}

			setState(root, 'connection');
		} catch (err) {
			setState(root, 'connection');
			if (unclear) {
				const text = root.querySelector('[data-ghahghah-view="connection"] .ghahghah-form__status-text');
				if (text) {
					text.textContent = 'نتیجه ارسال مشخص نیست. دوباره تلاش کنید.';
				}
			}
		} finally {
			root.dataset.submitting = '0';
		}
	};

	roots.forEach((root) => {
		bindProvinceCity(root);

		const preselect = root.getAttribute('data-preselect-product');
		if (preselect) {
			const productSelect = root.querySelector('[data-ghahghah-product], [name="product"]');
			if (productSelect && [...productSelect.options].some((o) => o.value === preselect)) {
				productSelect.value = preselect;
			}
		} else {
			const params = new URLSearchParams(window.location.search);
			const fromQuery = params.get('product');
			if (fromQuery) {
				const productSelect = root.querySelector('[data-ghahghah-product], [name="product"]');
				if (productSelect && [...productSelect.options].some((o) => o.value === fromQuery)) {
					productSelect.value = fromQuery;
				}
			}
		}

		const form = root.querySelector('form[data-ghahghah-view="idle"]');
		form?.addEventListener('submit', (event) => {
			event.preventDefault();
			submitForm(root);
		});
		root.querySelector('[data-ghahghah-retry]')?.addEventListener('click', () => {
			setState(root, 'idle');
			submitForm(root);
		});
	});
})();
