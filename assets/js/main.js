'use strict';

function slideUp(target, duration = 400) {
	target.style.height = target.offsetHeight + "px";
	target.style.boxSizing = "border-box";
	target.style.overflow = "hidden";

	target.offsetHeight;

	requestAnimationFrame(() => {
		requestAnimationFrame(() => {
			target.style.transition = `height ${duration}ms ease`;
			target.style.height = 0;
		});
	});

	setTimeout(() => {
		target.style.display = "none";
		target.style.removeProperty("height");
		target.style.removeProperty("overflow");
		target.style.removeProperty("transition");
	}, duration);
}

function slideDown(target, duration = 400) {
	target.style.removeProperty("display");

	let display = getComputedStyle(target).display;

	if (display === "none") display = "block";

	target.style.display = display;

	let height = target.offsetHeight;

	target.style.height = 0;
	target.style.overflow = "hidden";
	target.offsetHeight;

	requestAnimationFrame(() => {
		requestAnimationFrame(() => {
			target.style.transition = `height ${duration}ms ease`;
			target.style.height = height + "px";
		});
	});

	setTimeout(() => {
		target.style.removeProperty("height");
		target.style.removeProperty("overflow");
		target.style.removeProperty("transition");
	}, duration);
}

function slideToggle(target, duration = 500) {
	if (window.getComputedStyle(target).display === 'none') {
		slideDown(target, duration);
	} else {
		slideUp(target, duration);
	}
}

function getSiblings(elem) {
	let siblings = [];

	if (!elem.parentNode) {
		return siblings;
	}
	let sibling = elem.parentNode.firstChild;

	while (sibling) {
		if (sibling.nodeType === 1 && sibling !== elem) {
			siblings.push(sibling);
		}
		sibling = sibling.nextSibling;
	}
	return siblings;
}

function setTelMask() {
	[].forEach.call(document.querySelectorAll('[type="tel"]'), function (input) {
		let keyCode;

		function mask(event) {
			event.keyCode && (keyCode = event.keyCode);
			let pos = this.selectionStart;
			if (pos < 3) event.preventDefault();
			let matrix = input.placeholder,
				i = 0,
				def = matrix.replace(/\D/g, ""),
				val = this.value.replace(/\D/g, ""),
				new_value = matrix.replace(/[_\d]/g, function (a) {
					return i < val.length ? val.charAt(i++) || def.charAt(i) : a
				});
			i = new_value.indexOf("_");
			if (i != -1) {
				i < 5 && (i = 3);
				new_value = new_value.slice(0, i)
			}
			let reg = matrix.substr(0, this.value.length).replace(/_+/g,
				function (a) {
					return "\\d{1," + a.length + "}"
				}).replace(/[+()]/g, "\\$&");
			reg = new RegExp("^" + reg + "$");
			if (!reg.test(this.value) || this.value.length < 5 || keyCode > 47 && keyCode < 58) this.value = new_value;
			if (event.type == "blur" && this.value.length < 5) this.value = ""
		}

		input.addEventListener("input", mask, false);
		input.addEventListener("focus", mask, false);
		input.addEventListener("blur", mask, false);
		input.addEventListener("keydown", mask, false)
	});
}

function sendForms() {
	const startTime = Date.now();

	document.querySelectorAll('form.js-form').forEach(function (form) {
		const typingSpeed = [];
		form.querySelectorAll('input, textarea').forEach((field) => {
			let lastTime = null;
			field.addEventListener('input', () => {
				const now = Date.now();
				if (lastTime) typingSpeed.push(now - lastTime);
				lastTime = now;
			});
		});
		form.addEventListener('submit', async function (e) {
			e.preventDefault();

			const formData = new FormData(form);
			const formName = form.getAttribute('name');
			const submitBtn = form.querySelector('[type=submit]');
			if (!formName || !submitBtn) return;
			const submitBtnText = submitBtn.innerHTML;
			formData.set('form_name', formName);
			formData.set('time_on_page', Date.now() - startTime);
			formData.set('typing_speed', JSON.stringify(typingSpeed));
			formData.set('action', 'send_mail');
			submitBtn.disabled = true;
			submitBtn.innerHTML = 'Отправляю...';

			form.classList.add('loading');
			try {
				const response = await fetch(adem_ajax.url, { method: 'POST', body: formData });
				const result = await response.json();
				if (!response.ok || !result.success) {
					throw new Error(result.data?.message || 'Не удалось отправить форму.');
				}
				form.reset();
				form.dispatchEvent(new CustomEvent('adem:form-success', { bubbles: true, detail: result.data }));
				if (typeof Fancybox !== 'undefined' && document.querySelector('#modal-success')) {
					Fancybox.close(true);
					Fancybox.show([{ src: '#modal-success', type: 'inline' }]);
				}
			} catch (error) {
				form.dispatchEvent(new CustomEvent('adem:form-error', { bubbles: true, detail: error }));
			} finally {
				form.classList.remove('loading');
				submitBtn.disabled = false;
				submitBtn.innerHTML = submitBtnText;
			}
		});
	});
}

document.addEventListener("DOMContentLoaded", function () {
	if (typeof Fancybox !== 'undefined') Fancybox.bind();

	setTelMask();
	sendForms();
});
