function normalize(value) {
  return String(value || '').trim().toLowerCase();
}

export function enhanceSelect(select, options = {}) {
  if (!select || select.dataset.ezEnhanced === '1') {
    return;
  }

  select.dataset.ezEnhanced = '1';
  const wrapper = document.createElement('div');
  wrapper.className = 'ez-enhanced-select relative w-full';
  select.parentNode.insertBefore(wrapper, select);
  wrapper.appendChild(select);

  const search = document.createElement('input');
  search.type = 'search';
  search.className =
    'mb-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-primary-500';
  search.placeholder = options.placeholder || 'جست و جو کنید...';
  wrapper.insertBefore(search, select);

  const matcher = options.matcher;
  const optionsList = Array.from(select.options);

  const applyFilter = () => {
    const term = normalize(search.value);
    optionsList.forEach((option) => {
      if (!option.value) {
        option.hidden = false;
        return;
      }

      if (!term) {
        option.hidden = false;
        return;
      }

      if (typeof matcher === 'function') {
        const result = matcher({ term }, { id: option.value, text: option.text, element: option });
        option.hidden = result === null;
        return;
      }

      const haystack = `${option.text} ${option.value}`.toLowerCase();
      option.hidden = !haystack.includes(term);
    });
  };

  search.addEventListener('input', applyFilter);
  applyFilter();
}

export function initEnhancedSelects() {
  document.querySelectorAll('.select-box').forEach((select) => {
    if (select.id === 'user-city-select') {
      enhanceSelect(select, {
        placeholder: 'جست و جو کنید...',
        matcher(params, data) {
          if (!normalize(params.term)) {
            return data;
          }
          const term = normalize(params.term);
          const cityName = normalize(data.text);
          const citySlug = normalize(data.id);
          if (cityName.includes(term) || citySlug.includes(term)) {
            return data;
          }
          return null;
        },
      });
      return;
    }

    enhanceSelect(select);
  });
}

window.EzEnhancedSelect = {
  enhance: enhanceSelect,
  init: initEnhancedSelects,
};
