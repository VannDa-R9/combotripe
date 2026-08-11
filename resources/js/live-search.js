export function initLiveSearch() {
  const searchInputs = document.querySelectorAll('input[name="q"], [data-live-search]');
  if (searchInputs.length === 0) return;

  searchInputs.forEach(input => {
    if (input.dataset.liveSearchInit) return;
    input.dataset.liveSearchInit = 'true';

    // Find parent container or wrap
    let wrap = input.closest('.search-input-wrap');
    if (!wrap) {
      wrap = input.parentElement;
      wrap.classList.add('search-input-wrap');
    }

    // Create dropdown container
    let dropdown = wrap.querySelector('.search-dropdown');
    if (!dropdown) {
      dropdown = document.createElement('div');
      dropdown.className = 'search-dropdown';
      wrap.appendChild(dropdown);
    }

    setupInputListeners(input, dropdown);
  });
}

function setupInputListeners(input, dropdown) {
  let timer = null;
  let activeIndex = -1;
  let activeAbortController = null;

  const closeDropdown = () => {
    dropdown.classList.remove('open');
    dropdown.innerHTML = '';
    activeIndex = -1;
  };

  const getItems = () => Array.from(dropdown.querySelectorAll('.search-dropdown-item'));

  // Debounced input handler
  input.addEventListener('input', () => {
    const query = input.value.trim();

    if (timer) clearTimeout(timer);

    if (query.length < 2) {
      if (activeAbortController) activeAbortController.abort();
      closeDropdown();
      return;
    }

    // Show skeleton loading dropdown
    renderSkeleton(dropdown);

    timer = setTimeout(() => {
      fetchResults(query, dropdown);
    }, 300);
  });

  // Keyboard Navigation
  input.addEventListener('keydown', e => {
    if (!dropdown.classList.contains('open')) return;

    const items = getItems();
    if (items.length === 0) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      activeIndex = (activeIndex + 1) % items.length;
      updateActiveItem(items, activeIndex);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      activeIndex = (activeIndex - 1 + items.length) % items.length;
      updateActiveItem(items, activeIndex);
    } else if (e.key === 'Enter') {
      if (activeIndex >= 0 && items[activeIndex]) {
        e.preventDefault();
        items[activeIndex].click();
      }
    } else if (e.key === 'Escape') {
      closeDropdown();
    }
  });

  // Re-open on focus if query present
  input.addEventListener('focus', () => {
    if (input.value.trim().length >= 2 && dropdown.children.length > 0) {
      dropdown.classList.add('open');
    }
  });

  // Click Outside to close
  document.addEventListener('click', e => {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      closeDropdown();
    }
  });
}

function updateActiveItem(items, index) {
  items.forEach((item, i) => {
    if (i === index) {
      item.classList.add('active');
      item.scrollIntoView({ block: 'nearest' });
    } else {
      item.classList.remove('active');
    }
  });
}

function renderSkeleton(dropdown) {
  dropdown.innerHTML = '';
  dropdown.classList.add('open');

  const group = document.createElement('div');
  group.className = 'search-dropdown-group';

  for (let i = 0; i < 3; i++) {
    const item = document.createElement('div');
    item.className = 'search-dropdown-item';

    const thumb = document.createElement('div');
    thumb.className = 'search-dropdown-thumb skeleton';

    const info = document.createElement('div');
    info.className = 'search-dropdown-info';

    const titleSk = document.createElement('div');
    titleSk.className = 'skeleton skeleton-text medium';

    const metaSk = document.createElement('div');
    metaSk.className = 'skeleton skeleton-text short';

    info.appendChild(titleSk);
    info.appendChild(metaSk);
    item.appendChild(thumb);
    item.appendChild(info);
    group.appendChild(item);
  }

  dropdown.appendChild(group);
}

function fetchResults(query, dropdown) {
  fetch(`/api/live-search?q=${encodeURIComponent(query)}`)
    .then(r => {
      if (!r.ok) throw new Error('Search failed');
      return r.json();
    })
    .then(data => {
      renderDropdownData(data, query, dropdown);
    })
    .catch(err => {
      if (err.name === 'AbortError') return;
      dropdown.innerHTML = '';
      const errDiv = document.createElement('div');
      errDiv.className = 'search-dropdown-error';
      errDiv.textContent = 'Could not load search results. Please try again.';
      dropdown.appendChild(errDiv);
      dropdown.classList.add('open');
    });
}

function renderDropdownData(data, query, dropdown) {
  dropdown.innerHTML = '';

  const categoryLabels = {
    places: 'Places & Destinations',
    provinces: 'Provinces',
    foods: 'Local Foods',
    hotels: 'Hotels & Stays',
    tags: 'Category Tags'
  };

  let totalItems = 0;

  Object.keys(categoryLabels).forEach(key => {
    const list = data[key] || [];
    if (list.length === 0) return;

    totalItems += list.length;

    const group = document.createElement('div');
    group.className = 'search-dropdown-group';

    const groupTitle = document.createElement('div');
    groupTitle.className = 'search-dropdown-group-title';
    groupTitle.textContent = categoryLabels[key];
    group.appendChild(groupTitle);

    list.forEach(item => {
      const link = document.createElement('a');
      link.href = item.url;
      link.className = 'search-dropdown-item';

      if (item.image) {
        const img = document.createElement('img');
        img.src = item.image;
        img.alt = item.name;
        img.className = 'search-dropdown-thumb';
        img.onerror = () => { img.style.display = 'none'; };
        link.appendChild(img);
      } else {
        const placeholder = document.createElement('div');
        placeholder.className = 'search-dropdown-thumb';
        placeholder.style.background = 'var(--color-bg)';
        placeholder.style.display = 'flex';
        placeholder.style.alignItems = 'center';
        placeholder.style.justifyContent = 'center';
        placeholder.textContent = '📍';
        link.appendChild(placeholder);
      }

      const info = document.createElement('div');
      info.className = 'search-dropdown-info';

      const name = document.createElement('div');
      name.className = 'search-dropdown-name';
      name.textContent = item.name;

      const meta = document.createElement('div');
      meta.className = 'search-dropdown-meta';
      meta.textContent = item.meta;

      info.appendChild(name);
      info.appendChild(meta);
      link.appendChild(info);

      const badge = document.createElement('span');
      badge.className = 'search-dropdown-badge';
      badge.textContent = item.category;
      link.appendChild(badge);

      group.appendChild(link);
    });

    dropdown.appendChild(group);
  });

  if (totalItems === 0) {
    const empty = document.createElement('div');
    empty.className = 'search-dropdown-empty';
    empty.textContent = `No results found for "${query}"`;
    dropdown.appendChild(empty);
  }

  dropdown.classList.add('open');
}
