<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="space-y-6">
	<div class="flex items-center justify-between flex-wrap gap-4">
		<div>
			<h1 class="text-base font-extrabold lg:text-2xl">گزارش وضعیت سفارشات</h1>
		</div>
	</div>

	<div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
		<div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
			<div class="lg:col-span-3">
				<label for="osl-order-id" class="block text-sm font-medium text-slate-700 mb-1">شماره سفارش</label>
				<input type="number" id="osl-order-id" class="w-full h-11 border border-slate-105 bg-white rounded-xl outline-none px-4 text-sm" placeholder="مثال: 123456">
			</div>
			<div class="lg:col-span-3">
				<label for="osl-user-id" class="block text-sm font-medium text-slate-700 mb-1">شناسه کاربر</label>
				<input type="number" id="osl-user-id" class="w-full h-11 border border-slate-105 bg-white rounded-xl outline-none px-4 text-sm" placeholder="مثال: 1001">
			</div>
			<div class="lg:col-span-6 flex items-end gap-2">
				<button type="button" id="osl-search-btn" class="h-11 px-5 rounded-lg bg-focus-blue text-white hover:bg-blue-link transition-colors">جستجو</button>
				<button type="button" id="osl-clear-btn" class="h-11 px-5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 inline-flex items-center">پاک کردن</button>
				<button type="button" id="osl-delete-old-btn" class="h-11 px-5 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 inline-flex items-center">حذف لاگ‌های 3 ماه پیش</button>
				<span id="osl-loading" class="text-sm text-slate-500 hidden">در حال بارگذاری...</span>
			</div>
		</div>
	</div>

	<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
		<div class="overflow-x-auto">
			<table class="w-full min-w-d980">
				<thead class="bg-slate-50 border-b border-slate-200">
					<tr class="text-right text-sm text-slate-600">
						<th class="px-4 py-3">ردیف</th>
						<th class="px-4 py-3">شماره سفارش</th>
						<th class="px-4 py-3">کاربر</th>
						<th class="px-4 py-3">لاگ تغییر وضعیت</th>
						<th class="px-4 py-3">تابع استفاده شده</th>
						<th class="px-4 py-3">تاریخ</th>
					</tr>
				</thead>
				<tbody id="osl-tbody">
					<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">در حال بارگذاری...</td></tr>
				</tbody>
			</table>
		</div>
	</div>

	<div id="osl-pagination" class="bg-white border border-slate-200 rounded-xl p-3"></div>
</div>

<script>
const oslAjaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
const oslNonce = '<?php echo esc_attr( wp_create_nonce( 'team-ajax-nonce' ) ); ?>';

const oslOrderId = document.getElementById('osl-order-id');
const oslUserId = document.getElementById('osl-user-id');
const oslSearchBtn = document.getElementById('osl-search-btn');
const oslClearBtn = document.getElementById('osl-clear-btn');
const oslDeleteOldBtn = document.getElementById('osl-delete-old-btn');
const oslLoading = document.getElementById('osl-loading');
const oslTbody = document.getElementById('osl-tbody');
const oslPagination = document.getElementById('osl-pagination');

let oslCurrentPage = 1;

function oslSetLoading(loading) {
	oslLoading.classList.toggle('hidden', !loading);
}

async function oslLoad(page = 1) {
	oslCurrentPage = page;
	oslSetLoading(true);
	oslTbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">در حال بارگذاری...</td></tr>';

	const payload = new URLSearchParams();
	payload.set('action', 'team_ajax_handler');
	payload.set('callback', 'order_status_log_report_get');
	payload.set('nonce', oslNonce);
	payload.set('page', String(page));
	if (oslOrderId.value) payload.set('order_id', oslOrderId.value);
	if (oslUserId.value) payload.set('user_id', oslUserId.value);

	try {
		const res = await fetch(oslAjaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: payload.toString(),
		});
		const json = await res.json();
		if (!json || !json.success) {
			throw new Error((json && json.data && json.data.message) ? json.data.message : 'خطا در دریافت اطلاعات');
		}
		const data = json.data || {};
		oslTbody.innerHTML = data.html || '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">لاگی یافت نشد</td></tr>';
		oslPagination.innerHTML = data.pagination || '';
	} catch (e) {
		oslTbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-rose-600">خطا در دریافت لاگ‌ها</td></tr>';
		oslPagination.innerHTML = '';
	} finally {
		oslSetLoading(false);
	}
}

async function oslDeleteOld() {
	if (!window.confirm('آیا مطمئن هستید که می‌خواهید لاگ‌های 3 ماه پیش به قبل را حذف کنید؟')) {
		return;
	}
	oslSetLoading(true);
	const payload = new URLSearchParams();
	payload.set('action', 'team_ajax_handler');
	payload.set('callback', 'order_status_log_report_delete_old');
	payload.set('nonce', oslNonce);

	try {
		const res = await fetch(oslAjaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: payload.toString(),
		});
		const json = await res.json();
		if (!json || !json.success) {
			throw new Error((json && json.data && json.data.message) ? json.data.message : 'خطا در حذف لاگ‌های قدیمی');
		}
		alert((json.data && json.data.message) ? json.data.message : 'انجام شد');
		await oslLoad(oslCurrentPage);
	} catch (e) {
		alert(e && e.message ? e.message : 'خطای نامشخص');
	} finally {
		oslSetLoading(false);
	}
}

oslSearchBtn.addEventListener('click', function () {
	oslLoad(1);
});

oslClearBtn.addEventListener('click', function () {
	oslOrderId.value = '';
	oslUserId.value = '';
	oslLoad(1);
});

oslDeleteOldBtn.addEventListener('click', function () {
	oslDeleteOld();
});

oslPagination.addEventListener('click', function (e) {
	const btn = e.target.closest('.ez-osl-page');
	if (!btn) return;
	e.preventDefault();
	const page = parseInt(btn.getAttribute('data-page') || '1', 10);
	if (page > 0) {
		oslLoad(page);
	}
});

oslPagination.addEventListener('keypress', function (e) {
	if (e.target && e.target.id === 'ez-osl-current-page' && e.key === 'Enter') {
		e.preventDefault();
		const page = parseInt(e.target.value || '1', 10);
		if (page > 0) {
			oslLoad(page);
		}
	}
});

oslLoad(1);
</script>
