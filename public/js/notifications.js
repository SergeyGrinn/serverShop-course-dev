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

		if (invalidFields.length === 0) return;

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

document.querySelectorAll('.custom-file-input').forEach(input => {
	input.addEventListener('change', () => {
		const fileName = input.closest('.custom-file-picker').querySelector('.custom-file-name');
		fileName.textContent = input.files[0]?.name || 'No file chosen';
	});
});

document.querySelectorAll('.confirm-delete-form').forEach(form => {
	form.addEventListener('submit', event => {
		if (form.dataset.confirmed === 'true') {
			delete form.dataset.confirmed;
			return;
		}

		event.preventDefault();

		const modal = document.createElement('div');
		modal.className = 'delete-confirmation-overlay';
		modal.innerHTML = `
			<div class="delete-confirmation-modal" role="dialog" aria-modal="true" aria-labelledby="delete-confirmation-title">
				<h2 id="delete-confirmation-title">Delete this item?</h2>
				<p>This action cannot be undone.</p>
				<div class="delete-confirmation-actions">
					<button type="button" class="delete-confirmation-cancel">Cancel</button>
					<button type="button" class="delete-confirmation-submit">Delete</button>
				</div>
			</div>
		`;

		document.body.appendChild(modal);

		const closeModal = () => modal.remove();
		modal.querySelector('.delete-confirmation-cancel').addEventListener('click', closeModal);
		modal.addEventListener('click', event => {
			if (event.target === modal) closeModal();
		});
		modal.querySelector('.delete-confirmation-submit').addEventListener('click', () => {
			form.dataset.confirmed = 'true';
			closeModal();
			form.requestSubmit();
		});
	});
});
