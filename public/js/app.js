const autoHideMessages = document.querySelectorAll('.js-auto-hide');

autoHideMessages.forEach((message) => {
  setTimeout(() => {
    message.classList.add('fade-out');

    setTimeout(() => {
      message.remove();
    }, 400);
  }, 3000);
});

document.querySelectorAll('.js-confirm-form').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const question = form.dataset.confirm || 'Confirmer cette action ?';

    if (!window.confirm(question)) {
      event.preventDefault();
    }
  });
});

document.querySelectorAll('input[name^="is_closed"]').forEach((checkbox) => {
  const updateRow = () => {
    const row = checkbox.closest('tr');
    row?.querySelectorAll('input[type="time"]').forEach((input) => {
      input.disabled = checkbox.checked;
    });
  };

  checkbox.addEventListener('change', updateRow);
  updateRow();
});

const euroFormatter = new Intl.NumberFormat('fr-FR', {
  style: 'currency',
  currency: 'EUR',
});

document.querySelectorAll('.js-order-form').forEach((form) => {
  const summary = form.querySelector('.order-price-summary');
  const peopleInput = form.querySelector('.js-order-people');
  const cityInput = form.querySelector('.js-order-city');

  if (!summary || !peopleInput || !cityInput) {
    return;
  }

  const calculatePrice = () => {
    const basePrice = Number(summary.dataset.basePrice);
    const minimum = Number(summary.dataset.minPeople);
    const people = Number(peopleInput.value);
    const normalizedCity = cityInput.value
      .trim()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase();

    if (!people || people < minimum || normalizedCity === '') {
      return;
    }

    const menuPrice = basePrice * (people / minimum);
    const discount = people >= minimum + 5 ? menuPrice * 0.1 : 0;
    const delivery = normalizedCity === 'bordeaux' ? 0 : 5;
    const total = menuPrice + delivery - discount;

    summary.querySelector('.js-menu-price').textContent =
      euroFormatter.format(menuPrice);
    summary.querySelector('.js-delivery-price').textContent =
      euroFormatter.format(delivery);
    summary.querySelector('.js-discount').textContent =
      `- ${euroFormatter.format(discount)}`;
    summary.querySelector('.js-total-price').textContent =
      euroFormatter.format(total);
  };

  peopleInput.addEventListener('input', calculatePrice);
  cityInput.addEventListener('input', calculatePrice);
  calculatePrice();
});

document.querySelectorAll('.order-status-form').forEach((form) => {
  const statusSelect = form.querySelector('.js-order-status');
  const cancellationFields = form.querySelector('.js-cancellation-fields');

  if (!statusSelect || !cancellationFields) {
    return;
  }

  const updateCancellationFields = () => {
    const cancellationSelected = statusSelect.value === 'annulee';
    cancellationFields.hidden = !cancellationSelected;
    cancellationFields.querySelectorAll('select, textarea').forEach((field) => {
      field.required = cancellationSelected;
    });
  };

  statusSelect.addEventListener('change', updateCancellationFields);
  updateCancellationFields();
});
