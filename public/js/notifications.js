let activeNotification = null;
let notificationTimer = null;

function showNotification(message, type = 'info') {
	if (activeNotification) {
		activeNotification.remove();
		activeNotification = null;
	}

	if (notificationTimer) {
		clearTimeout(notificationTimer);
	}

	const notification = document.createElement('div');
	notification.className = `site-notification site-notification-${type}`;
	notification.setAttribute('role', 'status');
	notification.textContent = message;
	activeNotification = notification;

	document.body.appendChild(notification);

	requestAnimationFrame(() => {
		notification.classList.add('site-notification-visible');
	});

	notificationTimer = setTimeout(() => {
		notification.classList.remove('site-notification-visible');
		notification.addEventListener('transitionend', () => {
			notification.remove();
			if (activeNotification === notification) {
				activeNotification = null;
			}
	}, {once: true});
	}, 8000);
}

document.querySelectorAll('.notification-validation-form').forEach(form => {
	form.addEventListener('submit', event => {
		form.querySelectorAll('.client-validation-error').forEach(error => error.remove());

		const invalidFields = Array.from(form.elements).filter(field => {
			if (!field.willValidate) return false;

			return !field.validity.valid;
		});

		if (invalidFields.length === 0) {
			const hardwareToggle = form.querySelector('.built-in-hardware-toggle');
			if (hardwareToggle?.checked) {
				const hardwareInputs = form.querySelectorAll('.built-in-hardware-fields input[type="number"]');
				const hasHardwareValue = Array.from(hardwareInputs).some(input => input.value.trim() !== '');
				if (!hasHardwareValue) {
					event.preventDefault();
					hardwareToggle.focus();
					showNotification('Enter at least one built-in hardware value.', 'error');
				}
			}
			return;
		}

		const missingFields = invalidFields.filter(field => field.validity.valueMissing);
		const rangeField = invalidFields.find(field => field.validity.rangeUnderflow);
		const hasTypeMismatch = invalidFields.some(field => field.validity.typeMismatch);

		if (hasTypeMismatch && missingFields.length === 0 && !rangeField) {
			return;
		}

		event.preventDefault();
		const firstInvalidField = invalidFields[0];
		firstInvalidField.focus();

		if (!form.classList.contains('no-inline-validation-errors')) {
			missingFields.forEach(field => {
				const error = document.createElement('p');
				error.className = 'client-validation-error text-red-600 text-sm';
				error.textContent = field.dataset.validationMessage || 'This field is required.';
				field.after(error);
			});
		}

		if (rangeField) {
			showNotification(`Value must be greater than or equal to ${rangeField.min}.`, 'error');
		} else if (missingFields.length > 0) {
			showNotification('Insufficient information provided.', 'error');
		} else {
			showNotification('Please check the entered data.', 'error');
		}
	});
});

document.querySelectorAll('.phone-number-input').forEach(input => {
	const normalizePhoneInput = () => {
		const hasLeadingPlus = input.value.trim().startsWith('+');
		const digits = input.value.replace(/[^0-9]/g, '').slice(0, 15);
		input.value = (hasLeadingPlus ? '+' : '') + digits;
	};

	normalizePhoneInput();
	input.addEventListener('input', normalizePhoneInput);
});

document.querySelectorAll('.custom-file-input').forEach(input => {
	input.addEventListener('change', () => {
		const fileName = input.closest('.custom-file-picker').querySelector('.custom-file-name');
		fileName.textContent = input.files[0]?.name || 'No file chosen';
	});
});

document.querySelectorAll('.built-in-hardware-toggle').forEach(toggle => {
	const fields = toggle.closest('form').querySelector('.built-in-hardware-fields');

	const updateHardwareFields = () => {
		fields.classList.toggle('hidden', !toggle.checked);
	};

	toggle.addEventListener('change', updateHardwareFields);
	updateHardwareFields();
});

function showConfirmationDialog({title, message, confirmLabel, variant = 'admin'}) {
	const overlay = document.createElement('div');
	overlay.className = 'delete-confirmation-overlay';
	if (variant === 'user' || variant === 'user-danger') {
		overlay.classList.add('delete-confirmation-overlay-user');
	}
	if (variant === 'user-danger') overlay.classList.add('delete-confirmation-danger');

	const dialog = document.createElement('div');
	dialog.className = 'delete-confirmation-modal';
	dialog.setAttribute('role', 'dialog');
	dialog.setAttribute('aria-modal', 'true');
	dialog.setAttribute('aria-labelledby', 'confirmation-dialog-title');

	const heading = document.createElement('h2');
	heading.id = 'confirmation-dialog-title';
	heading.textContent = title;
	const description = document.createElement('p');
	description.textContent = message;
	const actions = document.createElement('div');
	actions.className = 'delete-confirmation-actions';
	const cancelButton = document.createElement('button');
	cancelButton.type = 'button';
	cancelButton.className = 'delete-confirmation-cancel';
	cancelButton.textContent = 'Cancel';
	const confirmButton = document.createElement('button');
	confirmButton.type = 'button';
	confirmButton.className = 'delete-confirmation-submit';
	confirmButton.textContent = confirmLabel;

	actions.append(cancelButton, confirmButton);
	dialog.append(heading, description, actions);
	overlay.append(dialog);
	document.body.append(overlay);

	return new Promise(resolve => {
		let settled = false;
		const close = confirmed => {
			if (settled) return;
			settled = true;
			document.removeEventListener('keydown', handleKeydown);
			overlay.remove();
			resolve(confirmed);
		};
		const handleKeydown = event => {
			if (event.key === 'Escape') close(false);
		};

		cancelButton.addEventListener('click', () => close(false));
		confirmButton.addEventListener('click', () => close(true));
		overlay.addEventListener('click', event => {
			if (event.target === overlay) close(false);
		});
		document.addEventListener('keydown', handleKeydown);
		cancelButton.focus();
	});
}

document.querySelectorAll('.confirm-delete-form').forEach(form => {
	form.addEventListener('submit', async event => {
		if (form.dataset.confirmed === 'true') {
			delete form.dataset.confirmed;
			return;
		}

		event.preventDefault();
		const confirmed = await showConfirmationDialog({
			title: 'Delete this item?',
			message: 'This action cannot be undone.',
			confirmLabel: 'Delete'
		});
		if (!confirmed) return;

		form.dataset.confirmed = 'true';
		form.requestSubmit();
	});
});
