const minPriceInput = document.querySelector('#filterMinPrice');
const maxPriceInput = document.querySelector('#filterMaxPrice');
const themeSelect = document.querySelector('#filterTheme');
const dietSelect = document.querySelector('#filterDiet');
const peopleInput = document.querySelector('#filterPeople');
const results = document.querySelector('#menuResults');
const menuItems = Array.from(document.querySelectorAll('[data-menu-result]'));
const emptyMessage = document.querySelector('#menuFilterEmpty');
const errorMessage = document.querySelector('#menuFilterError');
const statusMessage = document.querySelector('#menuFilterStatus');

let requestController = null;
let debounceTimer = null;

function filterUrl() {
  const url = new URL(window.location.href);
  url.search = '';
  url.searchParams.set('page', 'api-menus');

  const filters = [
    ['min_price', minPriceInput?.value],
    ['max_price', maxPriceInput?.value],
    ['theme', themeSelect?.value],
    ['diet', dietSelect?.value],
    ['people', peopleInput?.value],
  ];

  filters.forEach(([name, value]) => {
    if (value) {
      url.searchParams.set(name, value);
    }
  });

  return url;
}

function displayResults(menuIds) {
  const visibleIds = new Set(menuIds.map(Number));

  menuItems.forEach((item) => {
    item.hidden = !visibleIds.has(Number(item.dataset.menuResult));
  });

  const count = visibleIds.size;
  if (emptyMessage) {
    emptyMessage.hidden = count > 0;
  }
  if (statusMessage) {
    statusMessage.textContent = count > 1
      ? `${count} menus correspondent aux critères.`
      : `${count} menu correspond aux critères.`;
  }
}

async function filterMenus() {
  if (!results || menuItems.length === 0) {
    return;
  }

  requestController?.abort();
  const currentController = new AbortController();
  requestController = currentController;
  results.setAttribute('aria-busy', 'true');

  if (errorMessage) {
    errorMessage.hidden = true;
    errorMessage.textContent = '';
  }

  try {
    const response = await fetch(filterUrl(), {
      headers: { Accept: 'application/json' },
      signal: currentController.signal,
    });
    const payload = await response.json();

    if (!response.ok) {
      throw new Error(payload.error || 'Le filtrage est momentanément indisponible.');
    }

    displayResults(payload.menu_ids || []);
  } catch (error) {
    if (error.name !== 'AbortError' && errorMessage) {
      errorMessage.textContent = error.message;
      errorMessage.hidden = false;
    }
  } finally {
    if (requestController === currentController) {
      results.setAttribute('aria-busy', 'false');
    }
  }
}

function scheduleFilter() {
  window.clearTimeout(debounceTimer);
  debounceTimer = window.setTimeout(filterMenus, 250);
}

[minPriceInput, maxPriceInput, themeSelect, dietSelect, peopleInput].forEach((field) => {
  field?.addEventListener('input', scheduleFilter);
  field?.addEventListener('change', scheduleFilter);
});
