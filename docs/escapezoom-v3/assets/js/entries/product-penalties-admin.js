import htmx from 'htmx.org';
import Alpine from 'alpinejs';
import { ezFetch, wireHtmx as ezAjaxWireHtmx } from '../lib/ez-ajax.js';

if (typeof window !== 'undefined') {
  window.htmx = htmx;
}

const PERSIAN_MONTHS = [
  'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
  'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
];

const DATE_FIELD_IDS = [
  ['penalty_from', 'penalty_until', 'penalty_range_display'],
  ['created_from', 'created_until', 'created_range_display'],
  ['updated_from', 'updated_until', 'updated_range_display'],
  ['modal_active_from', 'modal_active_until', 'modal_active_range_display'],
];

let calendarInstance = null;
let activeDateContext = null;
let searchAbort = null;

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function gregorianToIsoDate(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

function formatPersianRangeLabel(startDate, endDate) {
  if (!startDate || !endDate) {
    return '';
  }
  const start = `${startDate.day} ${PERSIAN_MONTHS[startDate.month - 1]} ${startDate.year}`;
  const end = `${endDate.day} ${PERSIAN_MONTHS[endDate.month - 1]} ${endDate.year}`;
  return `${start} — ${end}`;
}

function formatIsoRangeLabel(fromIso, untilIso) {
  if (!fromIso || !untilIso) {
    return '';
  }
  return `${fromIso} — ${untilIso}`;
}

function getDateContext(fromId, untilId, displayId) {
  return { fromId, untilId, displayId };
}

function applyRangeToContext(range) {
  if (!activeDateContext || !range?.startGregorian || !range?.endGregorian) {
    return;
  }

  const fromIso = gregorianToIsoDate(range.startGregorian);
  const untilIso = gregorianToIsoDate(range.endGregorian);
  const fromEl = document.getElementById(activeDateContext.fromId);
  const untilEl = document.getElementById(activeDateContext.untilId);
  const displayEl = document.getElementById(activeDateContext.displayId);

  if (fromEl) {
    fromEl.value = fromIso;
  }
  if (untilEl) {
    untilEl.value = untilIso;
  }
  if (displayEl) {
    displayEl.textContent = formatPersianRangeLabel(range.startDate, range.endDate);
    displayEl.classList.remove('text-base-content/50');
    displayEl.classList.add('text-base-content');
  }
}

function clearActiveContext() {
  if (!activeDateContext) {
    return;
  }

  const fromEl = document.getElementById(activeDateContext.fromId);
  const untilEl = document.getElementById(activeDateContext.untilId);
  const displayEl = document.getElementById(activeDateContext.displayId);

  if (fromEl) {
    fromEl.value = '';
  }
  if (untilEl) {
    untilEl.value = '';
  }
  if (displayEl) {
    displayEl.textContent = 'انتخاب بازه زمانی';
    displayEl.classList.add('text-base-content/50');
    displayEl.classList.remove('text-base-content');
  }
}

function initPenaltiesCalendar() {
  if (typeof window.PersianCalendar !== 'function') {
    return;
  }

  if (!calendarInstance) {
    calendarInstance = new window.PersianCalendar({
      onDateRangeSelected: (range) => applyRangeToContext(range),
      onDateRangeCleared: () => clearActiveContext(),
    });
  }

  wireDateTriggers();
}

function syncDateDisplaysFromHidden() {
  DATE_FIELD_IDS.forEach(([fromId, untilId, displayId]) => {
    const fromEl = document.getElementById(fromId);
    const untilEl = document.getElementById(untilId);
    const displayEl = document.getElementById(displayId);
    if (!fromEl || !untilEl || !displayEl) {
      return;
    }
    if (fromEl.value && untilEl.value) {
      displayEl.textContent = formatIsoRangeLabel(fromEl.value, untilEl.value);
      displayEl.classList.remove('text-base-content/50');
      displayEl.classList.add('text-base-content');
    }
  });
}

function wireDateTriggers(root = document) {
  root.querySelectorAll('[data-ez-penalty-date-trigger]').forEach((btn) => {
    if (btn.dataset.ezPenaltyDateWired === '1') {
      return;
    }
    btn.dataset.ezPenaltyDateWired = '1';
    btn.addEventListener('click', () => {
      if (!calendarInstance && typeof window.PersianCalendar === 'function') {
        initPenaltiesCalendar();
      }
      activeDateContext = getDateContext(
        btn.dataset.fromId,
        btn.dataset.untilId,
        btn.dataset.displayId,
      );
      calendarInstance?.openCalendarModal();
    });
  });
  syncDateDisplaysFromHidden();
}

function renderSearchResults(container, items, { loading = false, empty = false } = {}) {
  if (!container) {
    return;
  }

  if (loading) {
    container.innerHTML = `
      <div class="flex items-center justify-center gap-2 py-6 text-sm text-base-content/60">
        <span>در حال جستجو</span>
        <span class="ez-penalty-search-dots" aria-hidden="true">...</span>
      </div>`;
    container.classList.remove('hidden');
    return;
  }

  if (empty || !items.length) {
    container.innerHTML = '<p class="text-center text-sm text-base-content/50 py-4">نتیجه‌ای یافت نشد</p>';
    container.classList.remove('hidden');
    return;
  }

  container.innerHTML = `<ul class="divide-y divide-base-200">${items
    .map((item) => {
      const img = item.image_url
        ? `<img src="${escapeHtml(item.image_url)}" alt="" class="w-7 h-8 rounded object-cover shrink-0" loading="lazy">`
        : '<span class="ez-penalty-search-thumb--empty" aria-hidden="true"></span>';
      return `<li>
        <button type="button" class="ez-penalty-search-hit w-full" data-product-id="${item.id}" data-product-title="${escapeHtml(item.title)}" data-product-image="${escapeHtml(item.image_url || '')}">
          <span class="flex items-center gap-3 min-w-0 flex-1">
            ${img}
            <span class="ez-penalty-search-title">${escapeHtml(item.title)}</span>
          </span>
          <span class="ez-penalty-search-id">#${item.id}</span>
        </button>
      </li>`;
    })
    .join('')}</ul>`;
  container.classList.remove('hidden');
}

async function fetchProductSearch(term) {
  if (searchAbort) {
    searchAbort.abort();
  }
  searchAbort = new AbortController();
  const resp = await ezFetch('penalty.product_search', { q: term }, { signal: searchAbort.signal });
  const json = await resp.json();
  if (!json?.ok) {
    return [];
  }
  return json.data?.items || [];
}

function wireProductSearch(root = document) {
  root.querySelectorAll('.ez-penalty-product-search-input').forEach((search) => {
    if (search.dataset.ezPenaltySearchWired === '1') {
      return;
    }
    search.dataset.ezPenaltySearchWired = '1';

    const resultsId = search.dataset.resultsId;
    const productFieldId = search.dataset.productIdField;
    const results = resultsId ? document.getElementById(resultsId) : null;
    const productId = productFieldId ? document.getElementById(productFieldId) : null;
    if (!results || !productId) {
      return;
    }

    let timer = null;

    const closeResults = () => {
      results.classList.add('hidden');
    };

    search.addEventListener('input', () => {
      clearTimeout(timer);
      productId.value = '';
      const term = search.value.trim();
      if (term.length < 2) {
        results.innerHTML = '';
        closeResults();
        return;
      }

      renderSearchResults(results, [], { loading: true });

      timer = setTimeout(() => {
        fetchProductSearch(term)
          .then((items) => {
            renderSearchResults(results, items, { empty: items.length === 0 });
          })
          .catch(() => {
            results.innerHTML = '<p class="text-center text-sm text-error py-4">خطا در جستجو</p>';
            results.classList.remove('hidden');
          });
      }, 350);
    });

    results.addEventListener('click', (event) => {
      const btn = event.target.closest('.ez-penalty-search-hit');
      if (!btn) {
        return;
      }
      const id = btn.getAttribute('data-product-id') || '';
      const title = btn.getAttribute('data-product-title') || '';
      const image = btn.getAttribute('data-product-image') || '';
      productId.value = id;
      search.value = title ? `${title} (#${id})` : '';
      closeResults();
      updateModalPreview(title, image);
    });

    document.addEventListener('click', (event) => {
      if (!search.contains(event.target) && !results.contains(event.target)) {
        closeResults();
      }
    });
  });
}

function updateModalPreview(title, imageUrl) {
  const preview = document.getElementById('ez_penalty_modal_selected_preview');
  if (!preview) {
    return;
  }
  if (!title) {
    preview.classList.add('hidden');
    preview.innerHTML = '';
    return;
  }
  const img = imageUrl
    ? `<img src="${escapeHtml(imageUrl)}" alt="" class="ez-penalty-selected-preview__img" loading="lazy">`
    : '';
  preview.innerHTML = `${img}<span class="ez-penalty-selected-preview__title">${escapeHtml(title)}</span>`;
  preview.classList.remove('hidden');
}

function restoreAddFormTemplate() {
  const tpl = document.getElementById('ez-penalty-form-add-template');
  const body = document.getElementById('ez-penalty-form-modal-body');
  if (!tpl || !body) {
    return;
  }
  body.innerHTML = tpl.innerHTML;
  wireProductSearch(body);
  wireDateTriggers(body);
  if (typeof Alpine !== 'undefined') {
    Alpine.initTree(body);
  }
}

document.addEventListener('alpine:init', () => {
  Alpine.data('ezPenaltyAdminPage', () => ({
    noteOpen: false,
    noteText: '',
    formOpen: false,

    init() {
      this.$el.addEventListener('penalty-show-note', (e) => {
        this.noteText = e.detail?.note || '';
        this.noteOpen = true;
      });

      initPenaltiesCalendar();
      wireProductSearch(this.$el);

      const body = document.getElementById('ez-penalty-form-modal-body');
      if (body) {
        wireProductSearch(body);
        wireDateTriggers(body);
      }
    },

    onApplyFilters() {
      const form = document.getElementById('ez-penalty-filters-form');
      if (!form) {
        return;
      }
      const paged = form.querySelector('input[name="paged"]');
      if (paged) {
        paged.value = '1';
      }
    },

    refreshList() {
      htmx.trigger(document.body, 'penalty-list-refresh');
    },

    resetFilters() {
      const form = document.getElementById('ez-penalty-filters-form');
      if (!form) {
        return;
      }
      form.reset();

      const filterProductId = document.getElementById('ez_penalty_filter_product_id');
      const filterSearch = document.getElementById('ez_penalty_filter_search');
      const filterResults = document.getElementById('ez_penalty_filter_results');
      if (filterProductId) {
        filterProductId.value = '';
      }
      if (filterSearch) {
        filterSearch.value = '';
      }
      if (filterResults) {
        filterResults.innerHTML = '';
        filterResults.classList.add('hidden');
      }

      DATE_FIELD_IDS.forEach(([fromId, untilId, displayId]) => {
        const fromEl = document.getElementById(fromId);
        const untilEl = document.getElementById(untilId);
        const displayEl = document.getElementById(displayId);
        if (fromEl) {
          fromEl.value = '';
        }
        if (untilEl) {
          untilEl.value = '';
        }
        if (displayEl) {
          displayEl.textContent = 'انتخاب بازه زمانی';
          displayEl.classList.add('text-base-content/50');
          displayEl.classList.remove('text-base-content');
        }
      });

      if (calendarInstance?.reset) {
        calendarInstance.reset();
      }

      const paged = form.querySelector('input[name="paged"]');
      if (paged) {
        paged.value = '1';
      }

      const apply = document.getElementById('ez-penalty-apply-filters');
      if (apply) {
        apply.click();
      }
    },

    closeFormModal() {
      this.formOpen = false;
    },

    openCreate() {
      restoreAddFormTemplate();
      this.formOpen = true;
    },

    async openEdit(id) {
      const body = document.getElementById('ez-penalty-form-modal-body');
      if (!body) {
        return;
      }

      try {
        const resp = await ezFetch('penalty.form', { id });
        const html = await resp.text();
        body.innerHTML = html;
        wireProductSearch(body);
        wireDateTriggers(body);
        if (typeof Alpine !== 'undefined') {
          Alpine.initTree(body);
        }
        this.formOpen = true;
      } catch {
        alert('خطا در بارگذاری فرم');
      }
    },
  }));
});

document.body.addEventListener('htmx:afterSwap', (event) => {
  const target = event.detail?.target;
  if (target?.id === 'ez-penalty-table-host' && typeof Alpine !== 'undefined') {
    Alpine.initTree(target);
  }
  if (target?.id === 'ez-penalty-form-modal-body') {
    wireProductSearch(target);
    wireDateTriggers(target);
    if (typeof Alpine !== 'undefined') {
      Alpine.initTree(target);
    }
  }
});

document.addEventListener('htmx:afterRequest', (event) => {
  const xhr = event.detail?.xhr;
  if (!xhr) {
    return;
  }
  const trigger = xhr.getResponseHeader('HX-Trigger');
  if (!trigger) {
    return;
  }
  try {
    const data = JSON.parse(trigger);
    if (data['penalty-saved']) {
      document.body.dispatchEvent(new CustomEvent('penalty-saved'));
    }
    if (data['penalty-deleted']) {
      document.body.dispatchEvent(new CustomEvent('penalty-deleted'));
    }
  } catch {
    /* ignore */
  }
});

window.ezPenaltyOnSaveResponse = function onSaveResponse(event) {
  const xhr = event.detail?.xhr;
  if (!xhr) {
    return;
  }
  if (xhr.status >= 200 && xhr.status < 300) {
    document.body.dispatchEvent(new CustomEvent('penalty-saved'));
    return;
  }
  try {
    const data = JSON.parse(xhr.responseText);
    const message = data?.data?.message || data?.message || 'خطا در ذخیره';
    alert(message);
  } catch {
    alert('خطا در ذخیره');
  }
};

(function bootstrapPenaltiesAdmin() {
  ezAjaxWireHtmx();
  Alpine.start();
})();
