/**
 * Shared renderer for booking.game_search JSON items (team/panel/CRM).
 *
 * @param {Array<{id?: number, title?: string, city?: string, image_url?: string}>} items
 * @returns {string}
 */
export function renderGameSearchItems(items) {
  if (!Array.isArray(items) || !items.length) {
    return '';
  }

  let html = '';
  items.forEach((item) => {
    const pid = parseInt(item?.id, 10);
    if (!Number.isFinite(pid) || pid <= 0) {
      return;
    }
    const title = escapeHtml(String(item?.title || ''));
    const city = escapeHtml(String(item?.city || ''));
    const imageUrl = escapeAttr(String(item?.image_url || ''));
    html += `<a href="javascript:;" data-id="${pid}" data-title="${title}" class="team_sans_game_search_item flex items-center gap-x-2 py-2">`;
    if (imageUrl) {
      html += `<img src="${imageUrl}" alt="" class="h-10 w-7.5 rounded" loading="lazy" decoding="async" referrerpolicy="no-referrer">`;
    }
    html += `<span>${title}`;
    if (city) {
      html += ` (${city})`;
    }
    html += '</span></a>';
  });

  return html;
}

/**
 * @param {string} text
 */
function escapeHtml(text) {
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/**
 * @param {string} text
 */
function escapeAttr(text) {
  return escapeHtml(text).replace(/'/g, '&#39;');
}
