<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="space-y-6">
	<div class="flex items-center justify-between flex-wrap gap-4">
		<div>
			<h1 class="text-base font-extrabold lg:text-2xl">گزارش رضایت سفارش</h1>
		</div>
	</div>

	<form id="satisfaction-report-form" class="grid grid-cols-1 lg:grid-cols-16 gap-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
		<input type="hidden" name="action" value="team_ajax_handler">
		<input type="hidden" name="callback" value="satisfaction_report_get">
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'team-ajax-nonce' ) ); ?>">
		<input type="hidden" name="game_id" id="selected-game-id" value="">

		<div class="lg:col-span-4">
			<label class="block text-sm font-medium text-slate-700 mb-1">جستجوی بازی</label>
			<div class="relative">
				<input id="gameSearch" type="text" autocomplete="off" class="w-full h-11 border border-slate-105 bg-white rounded-xl outline-none px-4 text-xs font-bold text-navyBlue" placeholder="نام بازی را جستجو کنید">
				<div id="lg-search-result-list" class="hidden absolute z-50 mt-1 w-full max-h-72 overflow-y-auto divide-y divide-slate-105 bg-white border border-slate-200 rounded-xl shadow-sm"></div>
			</div>
			<p id="selected-game-title" class="text-xs text-slate-500 mt-1">هیچ بازی انتخاب نشده</p>
		</div>

		<div class="lg:col-span-4">
			<label class="block text-sm font-medium text-slate-700 mb-1">بازه تاریخ</label>
			<div class="date-range-container relative">
				<div class="date-range-trigger h-11 px-3 bg-white border border-slate-105 rounded-xl cursor-pointer flex items-center justify-between" id="date-range-trigger">
					<span class="date-range-text text-slate-500 text-sm" id="main-date-range-text">انتخاب بازه زمانی</span>
					<div class="flex items-center gap-2">
						<button type="button" id="clear-date-range" class="clear-date-btn hidden text-gray-400 hover:text-gray-700" aria-label="clear date">
							✕
						</button>
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
							<path d="M1.5 9C1.5 6.17175 1.5 4.75725 2.379 3.879C3.258 3.00075 4.67175 3 7.5 3H10.5C13.3282 3 14.7427 3 15.621 3.879C16.4992 4.758 16.5 6.17175 16.5 9V10.5C16.5 13.3282 16.5 14.7427 15.621 15.621C14.742 16.4992 13.3282 16.5 10.5 16.5H7.5C4.67175 16.5 3.25725 16.5 2.379 15.621C1.50075 14.742 1.5 13.3282 1.5 10.5V9Z" stroke="#FF6900" stroke-width="1.5" />
							<path d="M5.25 3V1.875M12.75 3V1.875M1.875 6.75H16.125" stroke="#FF6900" stroke-width="1.5" stroke-linecap="round" />
						</svg>
					</div>
				</div>
				<input type="hidden" id="date-range-data" name="date_range" value="">
			</div>
		</div>

		<div class="lg:col-span-4">
			<label class="block text-sm font-medium text-slate-700 mb-1">بازه مقایسه</label>
			<div class="date-range-container relative">
				<div class="date-range-trigger h-11 px-3 bg-white border border-slate-105 rounded-xl cursor-pointer flex items-center justify-between" id="compare-date-range-trigger">
					<span class="date-range-text text-slate-500 text-sm" id="compare-date-range-text">انتخاب بازه مقایسه</span>
					<div class="flex items-center gap-2">
						<button type="button" id="clear-compare-date-range" class="clear-date-btn hidden text-gray-400 hover:text-gray-700" aria-label="clear compare date">
							✕
						</button>
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
							<path d="M1.5 9C1.5 6.17175 1.5 4.75725 2.379 3.879C3.258 3.00075 4.67175 3 7.5 3H10.5C13.3282 3 14.7427 3 15.621 3.879C16.4992 4.758 16.5 6.17175 16.5 9V10.5C16.5 13.3282 16.5 14.7427 15.621 15.621C14.742 16.4992 13.3282 16.5 10.5 16.5H7.5C4.67175 16.5 3.25725 16.5 2.379 15.621C1.50075 14.742 1.5 13.3282 1.5 10.5V9Z" stroke="#FF6900" stroke-width="1.5" />
							<path d="M5.25 3V1.875M12.75 3V1.875M1.875 6.75H16.125" stroke="#FF6900" stroke-width="1.5" stroke-linecap="round" />
						</svg>
					</div>
				</div>
				<input type="hidden" id="compare-date-range-data" name="compare_date_range" value="">
			</div>
		</div>

		<div class="lg:col-span-4 flex items-center justify-center gap-2">
			<button type="submit" class="h-11 px-5 rounded-lg bg-focus-blue text-white hover:bg-blue-link transition-colors">نمایش گزارش</button>
			<button type="button" id="satisfaction-clear-filters" class="h-11 px-5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 inline-flex items-center">حذف فیلتر</button>
		</div>
	</form>

	<div id="satisfaction-report-results"></div>

	<div id="satisfaction-detail-modal-overlay" class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-sm">
		<div class="w-full h-full flex items-center justify-center p-4">
			<div id="satisfaction-detail-modal" class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-modal overflow-hidden">
				<div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
					<p id="satisfaction-detail-modal-title" class="text-base font-extrabold text-slate-800">جزئیات</p>
					<button type="button" id="satisfaction-detail-modal-close" class="text-slate-500 hover:text-slate-800 text-xl leading-none">&times;</button>
				</div>
				<div id="satisfaction-detail-modal-body" class="p-4 overflow-y-auto max-h-[75vh] text-sm text-slate-700"></div>
			</div>
		</div>
	</div>
</div>

<?php include get_template_directory() . '/template/calendar/calendar-layout.php'; ?>
<script src="<?php echo esc_url( get_template_directory_uri() . '/assets/js/calendar-module.js' ); ?>"></script>
<script>
const ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';

const form = document.getElementById('satisfaction-report-form');
const resultsEl = document.getElementById('satisfaction-report-results');
const gameSearchInput = document.getElementById('gameSearch');
const gameSearchList = document.getElementById('lg-search-result-list');
const selectedGameIdInput = document.getElementById('selected-game-id');
const selectedGameTitleEl = document.getElementById('selected-game-title');
const dateRangeTrigger = document.getElementById('date-range-trigger');
const dateRangeText = document.getElementById('main-date-range-text');
const clearDateBtn = document.getElementById('clear-date-range');
const dateRangeData = document.getElementById('date-range-data');
const compareDateRangeTrigger = document.getElementById('compare-date-range-trigger');
const compareDateRangeText = document.getElementById('compare-date-range-text');
const clearCompareDateBtn = document.getElementById('clear-compare-date-range');
const compareDateRangeData = document.getElementById('compare-date-range-data');

let selectedDateRange = null;
let selectedCompareDateRange = null;
let gameSearchTimer = null;
let charts = {
	satReduction: null,
	breakdown: null,
	compareSatReduction: null,
	compareBreakdown: null,
};
let chartLoadingPromise = null;
let tableState = {
	page: 1,
	perPage: 20,
	sortBy: 'created_at',
	sortDir: 'desc',
};
const detailModalOverlay = document.getElementById('satisfaction-detail-modal-overlay');
const detailModalTitle = document.getElementById('satisfaction-detail-modal-title');
const detailModalBody = document.getElementById('satisfaction-detail-modal-body');
const detailModalClose = document.getElementById('satisfaction-detail-modal-close');

function escHtml(value) {
	return String(value === undefined || value === null ? '' : value)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');
}

function renderLoading() {
	resultsEl.innerHTML = '<div class="w-full rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-500">در حال دریافت گزارش...</div>';
}

function renderError(message) {
	resultsEl.innerHTML = '<div class="w-full rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">' + escHtml(message || 'خطا در دریافت داده') + '</div>';
}

function destroyCharts() {
	Object.keys(charts).forEach((key) => {
		if (charts[key]) {
			charts[key].destroy();
			charts[key] = null;
		}
	});
}

function loadScript(src) {
	return new Promise((resolve, reject) => {
		const script = document.createElement('script');
		script.src = src;
		script.async = true;
		script.onload = resolve;
		script.onerror = reject;
		document.head.appendChild(script);
	});
}

async function ensureChartCtor() {
	if (window.Chart && typeof window.Chart === 'function') {
		return window.Chart;
	}
	if (!chartLoadingPromise) {
		chartLoadingPromise = (async function() {
			try {
				await loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js');
			} catch (e) {
				await loadScript('https://unpkg.com/chart.js@4.4.3/dist/chart.umd.min.js');
			}
			if (!window.Chart || typeof window.Chart !== 'function') {
				throw new Error('Chart library was not loaded');
			}
			return window.Chart;
		})();
	}
	return chartLoadingPromise;
}

function drawDoughnut(Chart, canvasId, labels, values, colors) {
	const canvas = document.getElementById(canvasId);
	if (!canvas) {
		return null;
	}
	return new Chart(canvas, {
		type: 'doughnut',
		data: {
			labels: labels || [],
			datasets: [{
				data: values || [],
				backgroundColor: colors || [],
				borderWidth: 1,
			}],
		},
		options: {
			responsive: true,
			plugins: {
				legend: { position: 'bottom' },
			},
		},
	});
}

async function renderCharts(reportPayload) {
	const currentData = reportPayload && reportPayload.current ? reportPayload.current : {};
	const compareData = reportPayload && reportPayload.compare ? reportPayload.compare : null;
	const chartData = currentData && currentData.chart ? currentData.chart : {};
	const satReduction = chartData.satisfaction_vs_reduction || { labels: [], data: [] };
	const breakdown = chartData.reduction_breakdown || { labels: [], data: [] };
	const Chart = await ensureChartCtor();
	destroyCharts();

	charts.satReduction = drawDoughnut(Chart, 'sat-reduction-chart', satReduction.labels, satReduction.data, ['#10B981', '#F97316']);
	charts.breakdown = drawDoughnut(Chart, 'reduction-breakdown-chart', breakdown.labels, breakdown.data, ['#F59E0B', '#6366F1', '#94A3B8']);

	if (compareData && compareData.chart) {
		const compareSat = compareData.chart.satisfaction_vs_reduction || { labels: [], data: [] };
		const compareBreak = compareData.chart.reduction_breakdown || { labels: [], data: [] };
		charts.compareSatReduction = drawDoughnut(Chart, 'compare-sat-reduction-chart', compareSat.labels, compareSat.data, ['#34D399', '#FB923C']);
		charts.compareBreakdown = drawDoughnut(Chart, 'compare-reduction-breakdown-chart', compareBreak.labels, compareBreak.data, ['#FBBF24', '#818CF8', '#94A3B8']);
	}
}

function asNumber(value) {
	const n = Number(value);
	return Number.isFinite(n) ? n : 0;
}

function formatDelta(value) {
	const normalized = asNumber(value);
	if (normalized > 0) return `+${normalized}`;
	return `${normalized}`;
}

function kpiCell(title, colorClass, percent, count, deltaPercent, deltaCount) {
	const hasDelta = deltaPercent !== null && deltaPercent !== undefined && deltaCount !== null && deltaCount !== undefined;
	const deltaText = hasDelta
		? `<p class="text-xs text-slate-500 mt-1">تغییر نسبت به بازه مقایسه: ${escHtml(formatDelta(deltaPercent))}% (${escHtml(formatDelta(deltaCount))})</p>`
		: '';
	return `
		<div class="bg-white border rounded-xl p-4 border-slate-200">
			<p class="text-sm text-slate-600">${escHtml(title)}</p>
			<p class="text-2xl font-extrabold mt-2 ${escHtml(colorClass)}">${escHtml(percent)}%</p>
			<p class="text-xs text-slate-500 mt-1">تعداد: <strong>${escHtml(count)}</strong></p>
			${deltaText}
		</div>`;
}

function getSortIndicator(column) {
	if (tableState.sortBy !== column) {
		return '↕';
	}
	return tableState.sortDir === 'asc' ? '↑' : '↓';
}

function renderSortableTh(label, column) {
	return `<button type="button" class="js-sort-col inline-flex items-center gap-1 font-bold text-slate-600 hover:text-slate-900" data-sort="${escHtml(column)}">
		<span>${escHtml(label)}</span>
		<span class="text-xs">${escHtml(getSortIndicator(column))}</span>
	</button>`;
}

function renderPager(meta) {
	const page = Number(meta && meta.page ? meta.page : 1);
	const totalPages = Number(meta && meta.total_pages ? meta.total_pages : 1);
	if (!Number.isFinite(totalPages) || totalPages <= 1) {
		return '';
	}
	const buttons = [];
	const start = Math.max(1, page - 2);
	const end = Math.min(totalPages, page + 2);
	buttons.push(`<button type="button" class="js-report-page px-3 py-1.5 rounded-md border border-slate-300 text-sm ${page <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-100'}" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>قبلی</button>`);
	for (let p = start; p <= end; p++) {
		buttons.push(`<button type="button" class="js-report-page px-3 py-1.5 rounded-md border text-sm ${p === page ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-300 hover:bg-slate-100'}" data-page="${p}">${p}</button>`);
	}
	buttons.push(`<button type="button" class="js-report-page px-3 py-1.5 rounded-md border border-slate-300 text-sm ${page >= totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-100'}" data-page="${page + 1}" ${page >= totalPages ? 'disabled' : ''}>بعدی</button>`);
	return `<div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-200">
		<p class="text-xs text-slate-500">صفحه ${escHtml(page)} از ${escHtml(totalPages)}</p>
		<div class="flex items-center gap-2">${buttons.join('')}</div>
	</div>`;
}

function renderReport(data) {
	const current = data && data.current ? data.current : {};
	const compare = data && data.compare ? data.compare : null;
	const delta = data && data.delta ? data.delta : null;
	const kpi = current && current.kpi ? current.kpi : {};
	const meta = current && current.meta ? current.meta : {};
	const tableMeta = current && current.table_meta ? current.table_meta : {};
	const rows = Array.isArray(current && current.rows) ? current.rows : [];
	const satPair = kpi.sat || { percent: 0, count: 0 };
	const reductionPair = kpi.reduction || { percent: 0, count: 0 };
	const commentPair = kpi.comment_share || { percent: 0, count: 0 };
	const cancelPair = kpi.cancel_share || { percent: 0, count: 0 };
	const compareBadge = compare ? '<span class="inline-flex mr-2 px-2 py-1 text-xs rounded-md bg-indigo-50 text-indigo-700">با مقایسه</span>' : '';

	tableState.page = asNumber(tableMeta.page) > 0 ? asNumber(tableMeta.page) : tableState.page;
	tableState.perPage = asNumber(tableMeta.per_page) > 0 ? asNumber(tableMeta.per_page) : tableState.perPage;
	tableState.sortBy = tableMeta.sort_by || tableState.sortBy;
	tableState.sortDir = tableMeta.sort_dir || tableState.sortDir;

	let tableRows = '';
	if (!rows.length) {
		const emptyMessage = meta && meta.game_has_any_history === false
			? 'اطلاعات رضایت این بازی در دسترس نیست.'
			: 'داده‌ای با فیلتر فعلی پیدا نشد.';
		tableRows = `<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">${escHtml(emptyMessage)}</td></tr>`;
	} else {
		rows.forEach((row) => {
			const badgeCls = row.new_status === 'SATISFIED'
				? 'bg-emerald-100 text-emerald-700'
				: (row.new_status === 'DISSATISFIED' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700');
			const detailsPreview = row && row.details ? String(row.details).slice(0, 180) : '';
			const viewCommentBtn = row && row.has_comment_detail
				? `<button type="button" class="js-view-comment-detail text-xs px-2 py-1 rounded border border-slate-300 text-blue-700 hover:bg-blue-50" data-comment-id="${escHtml(row.comment_id || 0)}">مشاهده کامنت</button>`
				: '';
			const viewCancelBtn = row && row.has_owner_cancel_detail
				? `<button type="button" class="js-view-owner-cancel-detail text-xs px-2 py-1 rounded border border-slate-300 text-indigo-700 hover:bg-indigo-50" data-request-id="${escHtml(row.cancellation_request_id || 0)}" data-order-id="${escHtml(row.order_id || 0)}">مشاهده کنسلی مالک</button>`
				: '';
			const actionButtons = [viewCommentBtn, viewCancelBtn].filter(Boolean).join(' ');
			tableRows += `<tr class="border-b border-slate-100 text-sm">
				<td class="px-4 py-3">${escHtml(row.order_id)}</td>
				<td class="px-4 py-3">${escHtml(row.old_status_label || row.old_status || '-')}</td>
				<td class="px-4 py-3"><span class="px-2 py-1 rounded-md text-xs font-bold ${badgeCls}">${escHtml(row.new_status_label || row.new_status || '-')}</span></td>
				<td class="px-4 py-3">${escHtml(row.source_label || row.source || '-')}</td>
				<td class="px-4 py-3 max-w-d380 whitespace-pre-wrap break-words text-slate-600">
					<div>${escHtml(detailsPreview)}${(row.details || '').length > 180 ? '...' : ''}</div>
					${actionButtons ? `<div class="flex flex-wrap gap-2 mt-2">${actionButtons}</div>` : ''}
				</td>
				<td class="px-4 py-3">${escHtml(row.created_at_jalali || row.created_at || '-')}</td>
				<td class="px-4 py-3">${escHtml(row.updated_at_jalali || row.updated_at || '-')}</td>
			</tr>`;
		});
	}

	const pagerTop = renderPager(tableMeta);
	const pagerBottom = tableRows ? renderPager(tableMeta).replace('border-b', 'border-t') : '';

	resultsEl.innerHTML = `
		<div class="p-1">
			<p class="text-lg md:text-xl font-extrabold text-slate-700">نمایش نتایج ${compareBadge}</p>
		</div>
		<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mt-3">
			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
				${kpiCell('رضایت کل', 'text-emerald-600', satPair.percent || 0, satPair.count || 0, delta ? delta.sat_percent : null, delta ? delta.sat_count : null)}
				${kpiCell('کاهش کل', 'text-rose-600', reductionPair.percent || 0, reductionPair.count || 0, delta ? delta.reduction_percent : null, delta ? delta.reduction_count : null)}
				${kpiCell('سهم کامنت منفی از کاهش', 'text-amber-600', commentPair.percent || 0, commentPair.count || 0, delta ? delta.comment_share_percent : null, delta ? delta.comment_share_count : null)}
				${kpiCell('سهم کنسلی از کاهش', 'text-indigo-600', cancelPair.percent || 0, cancelPair.count || 0, delta ? delta.cancel_share_percent : null, delta ? delta.cancel_share_count : null)}
			</div>
			<div class="bg-white border border-slate-200 rounded-xl p-4">
				<p class="text-sm text-slate-600 mb-3">نمودار رضایت در برابر کاهش</p>
				<canvas id="sat-reduction-chart" height="180"></canvas>
			</div>
			<div class="bg-white border border-slate-200 rounded-xl p-4">
				<p class="text-sm text-slate-600 mb-3">ترکیب کاهش (کامنت / کنسلی / سایر)</p>
				<canvas id="reduction-breakdown-chart" height="180"></canvas>
			</div>
		</div>

		<div class="bg-white border border-slate-200 rounded-xl overflow-hidden mt-4">
			${pagerTop}
			<div class="overflow-x-auto">
				<table class="w-full min-w-d920">
					<thead class="bg-slate-50 border-b border-slate-200">
						<tr class="text-right text-sm text-slate-600">
							<th class="px-4 py-3">شناسه سفارش</th>
							<th class="px-4 py-3">${renderSortableTh('وضعیت قدیم', 'old_status')}</th>
							<th class="px-4 py-3">${renderSortableTh('وضعیت جدید', 'new_status')}</th>
							<th class="px-4 py-3">${renderSortableTh('منبع', 'source')}</th>
							<th class="px-4 py-3">جزئیات</th>
							<th class="px-4 py-3">${renderSortableTh('تاریخ ایجاد', 'created_at')}</th>
							<th class="px-4 py-3">${renderSortableTh('زمان بروزرسانی', 'updated_at')}</th>
						</tr>
					</thead>
					<tbody>${tableRows}</tbody>
				</table>
			</div>
			${pagerBottom}
		</div>`;
}

async function fetchReport() {
	if (!selectedGameIdInput.value) {
		renderError('انتخاب بازی الزامی است.');
		return;
	}
	renderLoading();
	const payload = new URLSearchParams(new FormData(form));
	payload.set('page', String(tableState.page));
	payload.set('per_page', String(tableState.perPage));
	payload.set('sort_by', tableState.sortBy);
	payload.set('sort_dir', tableState.sortDir);
	if (selectedDateRange && selectedDateRange.startGregorian && selectedDateRange.endGregorian) {
		const s = selectedDateRange.startGregorian;
		const e = selectedDateRange.endGregorian;
		const startDate = `${s.getFullYear()}-${String(s.getMonth() + 1).padStart(2, '0')}-${String(s.getDate()).padStart(2, '0')}`;
		const endDate = `${e.getFullYear()}-${String(e.getMonth() + 1).padStart(2, '0')}-${String(e.getDate()).padStart(2, '0')}`;
		payload.set('date_range', 'calendar');
		payload.set('start_date', startDate);
		payload.set('end_date', endDate);
	}
	if (selectedCompareDateRange && selectedCompareDateRange.startGregorian && selectedCompareDateRange.endGregorian) {
		const s = selectedCompareDateRange.startGregorian;
		const e = selectedCompareDateRange.endGregorian;
		const startDate = `${s.getFullYear()}-${String(s.getMonth() + 1).padStart(2, '0')}-${String(s.getDate()).padStart(2, '0')}`;
		const endDate = `${e.getFullYear()}-${String(e.getMonth() + 1).padStart(2, '0')}-${String(e.getDate()).padStart(2, '0')}`;
		payload.set('compare_date_range', 'calendar');
		payload.set('compare_start_date', startDate);
		payload.set('compare_end_date', endDate);
	}
	try {
		const res = await fetch(ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: payload.toString(),
		});
		const json = await res.json();
		if (!json || !json.success) {
			throw new Error(json && json.data && json.data.message ? json.data.message : 'خطا در دریافت داده');
		}
		renderReport(json.data || {});
		try {
			await renderCharts(json.data || {});
		} catch (chartErr) {
			console.error(chartErr);
		}
	} catch (err) {
		destroyCharts();
		renderError(err && err.message ? err.message : 'خطای نامشخص');
	}
}

async function searchGames(term) {
	if (!term || term.trim().length < 2) {
		gameSearchList.classList.add('hidden');
		gameSearchList.innerHTML = '';
		return;
	}
	const payload = new URLSearchParams();
	payload.set('action', 'team_ajax_handler');
	payload.set('callback', 'satisfaction_report_games_search');
	payload.set('nonce', form.querySelector('input[name="nonce"]').value);
	payload.set('term', term.trim());
	try {
		const res = await fetch(ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: payload.toString(),
		});
		const json = await res.json();
		if (!json || !json.success) {
			throw new Error('search failed');
		}
		const items = Array.isArray(json.data && json.data.items) ? json.data.items : [];
		if (!items.length) {
			gameSearchList.innerHTML = '<div class="px-3 py-3 text-sm text-slate-500">نتیجه‌ای یافت نشد.</div>';
			gameSearchList.classList.remove('hidden');
			return;
		}
		gameSearchList.innerHTML = items.map((item) => {
			const image = item.game_image_url
				? `<img src="${escHtml(item.game_image_url)}" alt="${escHtml(item.game_title)}" class="w-10 h-10 rounded-md object-cover border border-slate-200">`
				: '<div class="w-10 h-10 rounded-md bg-slate-100 border border-slate-200"></div>';
			const typeLabel = item.game_type ? `<p class="text-11 text-slate-500 mt-0.5">نوع: ${escHtml(item.game_type)}</p>` : '';
			const historyBadge = item.has_satisfaction_data
				? '<span class="text-10 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">دارای لاگ رضایت</span>'
				: '<span class="text-10 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">بدون لاگ رضایت</span>';
			return `<a href="javascript:;" class="team_sans_game_search_item flex items-center gap-2 px-3 py-2 hover:bg-slate-50" data-id="${escHtml(item.game_id)}" data-title="${escHtml(item.game_title)}" data-type="${escHtml(item.game_type || '')}" data-image="${escHtml(item.game_image_url || '')}" data-has-history="${item.has_satisfaction_data ? '1' : '0'}">
				${image}
				<div class="min-w-0 flex-1">
					<p class="text-sm font-semibold text-slate-700 truncate">${escHtml(item.game_title)}</p>
					${typeLabel}
				</div>
				<div>${historyBadge}</div>
			</a>`;
		}).join('');
		gameSearchList.classList.remove('hidden');
	} catch (e) {
		gameSearchList.innerHTML = '<div class="px-3 py-3 text-sm text-rose-500">خطا در جستجو</div>';
		gameSearchList.classList.remove('hidden');
	}
}

function closeDetailModal() {
	detailModalOverlay.classList.add('hidden');
	detailModalTitle.textContent = 'جزئیات';
	detailModalBody.innerHTML = '';
}

function openDetailModal(title, htmlContent) {
	detailModalTitle.textContent = title || 'جزئیات';
	detailModalBody.innerHTML = htmlContent || '<p class="text-slate-500">موردی برای نمایش وجود ندارد.</p>';
	detailModalOverlay.classList.remove('hidden');
}

async function fetchDetailModal(detailType, payloadExtra) {
	const payload = new URLSearchParams();
	payload.set('action', 'team_ajax_handler');
	payload.set('callback', 'satisfaction_report_detail_get');
	payload.set('nonce', form.querySelector('input[name="nonce"]').value);
	payload.set('detail_type', detailType);
	Object.keys(payloadExtra || {}).forEach((k) => {
		payload.set(k, String(payloadExtra[k]));
	});
	openDetailModal('در حال بارگذاری...', '<div class="text-slate-500">در حال دریافت جزئیات...</div>');
	try {
		const res = await fetch(ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: payload.toString(),
		});
		const json = await res.json();
		if (!json || !json.success) {
			throw new Error(json && json.data && json.data.message ? json.data.message : 'خطا در دریافت جزئیات');
		}
		const data = json.data || {};
		openDetailModal(data.title || 'جزئیات', data.html || '<p class="text-slate-500">جزئیات یافت نشد.</p>');
	} catch (err) {
		openDetailModal('خطا', `<div class="text-rose-600">${escHtml(err && err.message ? err.message : 'خطای نامشخص')}</div>`);
	}
}

function clearDateRange() {
	selectedDateRange = null;
	dateRangeData.value = '';
	dateRangeText.textContent = 'انتخاب بازه زمانی';
	dateRangeText.classList.add('text-slate-500');
	clearDateBtn.classList.add('hidden');
}

function clearCompareDateRange() {
	selectedCompareDateRange = null;
	compareDateRangeData.value = '';
	compareDateRangeText.textContent = 'انتخاب بازه مقایسه';
	compareDateRangeText.classList.add('text-slate-500');
	clearCompareDateBtn.classList.add('hidden');
}

const calendar = new PersianCalendar({
	onDateRangeSelected: function(dateRange) {
		selectedDateRange = dateRange;
		if (!dateRange || !dateRange.startDate || !dateRange.endDate) {
			clearDateRange();
			return;
		}
		const persianMonths = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
		const startLabel = `${dateRange.startDate.day} ${persianMonths[dateRange.startDate.month - 1]} ${dateRange.startDate.year}`;
		const endLabel = `${dateRange.endDate.day} ${persianMonths[dateRange.endDate.month - 1]} ${dateRange.endDate.year}`;
		dateRangeText.textContent = `${startLabel} تا ${endLabel}`;
		dateRangeData.value = `${startLabel} - ${endLabel}`;
		dateRangeText.classList.remove('text-slate-500');
		clearDateBtn.classList.remove('hidden');
	},
	onDateRangeCleared: function() {
		clearDateRange();
	},
});

const compareCalendar = new PersianCalendar({
	onDateRangeSelected: function(dateRange) {
		selectedCompareDateRange = dateRange;
		if (!dateRange || !dateRange.startDate || !dateRange.endDate) {
			clearCompareDateRange();
			return;
		}
		const persianMonths = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
		const startLabel = `${dateRange.startDate.day} ${persianMonths[dateRange.startDate.month - 1]} ${dateRange.startDate.year}`;
		const endLabel = `${dateRange.endDate.day} ${persianMonths[dateRange.endDate.month - 1]} ${dateRange.endDate.year}`;
		compareDateRangeText.textContent = `${startLabel} تا ${endLabel}`;
		compareDateRangeData.value = `${startLabel} - ${endLabel}`;
		compareDateRangeText.classList.remove('text-slate-500');
		clearCompareDateBtn.classList.remove('hidden');
	},
	onDateRangeCleared: function() {
		clearCompareDateRange();
	},
});

dateRangeTrigger.addEventListener('click', function(e) {
	e.preventDefault();
	calendar.openCalendarModal();
});

compareDateRangeTrigger.addEventListener('click', function(e) {
	e.preventDefault();
	compareCalendar.openCalendarModal();
});

clearDateBtn.addEventListener('click', function(e) {
	e.preventDefault();
	e.stopPropagation();
	clearDateRange();
});

clearCompareDateBtn.addEventListener('click', function(e) {
	e.preventDefault();
	e.stopPropagation();
	clearCompareDateRange();
});

gameSearchInput.addEventListener('input', function() {
	const term = gameSearchInput.value || '';
	selectedGameIdInput.value = '';
	selectedGameTitleEl.textContent = 'هیچ بازی انتخاب نشده';
	if (gameSearchTimer) {
		clearTimeout(gameSearchTimer);
	}
	gameSearchTimer = setTimeout(() => searchGames(term), 250);
});

document.addEventListener('click', function(e) {
	if (!e.target.closest('.team_sans_game_search_item') && !e.target.closest('#gameSearch')) {
		gameSearchList.classList.add('hidden');
	}
});

gameSearchList.addEventListener('click', function(e) {
	const item = e.target.closest('.team_sans_game_search_item');
	if (!item) {
		return;
	}
	const gid = item.getAttribute('data-id') || '';
	const title = item.getAttribute('data-title') || '';
	const type = item.getAttribute('data-type') || '';
	const hasHistory = item.getAttribute('data-has-history') === '1';
	selectedGameIdInput.value = gid;
	gameSearchInput.value = title;
	selectedGameTitleEl.textContent = type ? `${title} (${type})` : title;
	gameSearchList.classList.add('hidden');
	if (!hasHistory) {
		resultsEl.innerHTML = '<div class="w-full rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">اطلاعات رضایت این بازی در دسترس نیست.</div>';
	}
});

form.addEventListener('submit', function(e) {
	e.preventDefault();
	tableState.page = 1;
	fetchReport();
});

resultsEl.addEventListener('click', function(e) {
	const sortBtn = e.target.closest('.js-sort-col');
	if (sortBtn) {
		const col = sortBtn.getAttribute('data-sort') || '';
		if (!col) {
			return;
		}
		if (tableState.sortBy === col) {
			tableState.sortDir = tableState.sortDir === 'asc' ? 'desc' : 'asc';
		} else {
			tableState.sortBy = col;
			tableState.sortDir = 'desc';
		}
		tableState.page = 1;
		fetchReport();
		return;
	}

	const pageBtn = e.target.closest('.js-report-page');
	if (pageBtn) {
		const page = Number(pageBtn.getAttribute('data-page') || '1');
		if (Number.isFinite(page) && page > 0) {
			tableState.page = page;
			fetchReport();
		}
		return;
	}

	const commentBtn = e.target.closest('.js-view-comment-detail');
	if (commentBtn) {
		const commentId = Number(commentBtn.getAttribute('data-comment-id') || '0');
		if (commentId > 0) {
			fetchDetailModal('comment', { comment_id: commentId });
		}
		return;
	}

	const cancelBtn = e.target.closest('.js-view-owner-cancel-detail');
	if (cancelBtn) {
		const requestId = Number(cancelBtn.getAttribute('data-request-id') || '0');
		const orderId = Number(cancelBtn.getAttribute('data-order-id') || '0');
		fetchDetailModal('owner_cancel', { request_id: requestId, order_id: orderId });
	}
});

detailModalClose.addEventListener('click', closeDetailModal);
detailModalOverlay.addEventListener('click', function(e) {
	if (e.target === detailModalOverlay) {
		closeDetailModal();
	}
});
document.addEventListener('keydown', function(e) {
	if (e.key === 'Escape' && !detailModalOverlay.classList.contains('hidden')) {
		closeDetailModal();
	}
});

document.getElementById('satisfaction-clear-filters').addEventListener('click', function() {
	selectedGameIdInput.value = '';
	gameSearchInput.value = '';
	selectedGameTitleEl.textContent = 'هیچ بازی انتخاب نشده';
	clearDateRange();
	clearCompareDateRange();
	destroyCharts();
	tableState = { page: 1, perPage: 20, sortBy: 'created_at', sortDir: 'desc' };
	closeDetailModal();
	resultsEl.innerHTML = '';
});
</script>
