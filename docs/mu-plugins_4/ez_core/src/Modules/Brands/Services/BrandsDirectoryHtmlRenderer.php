<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\Services;

final class BrandsDirectoryHtmlRenderer
{
	public function buildPushUrlForPage(string $listingBase, int $pageNum): string
	{
		$pageNum = max(1, $pageNum);
		$base = explode('#', $listingBase, 2)[0];
		$base = preg_replace('/[?&]page=\d+/', '', $base) ?? $base;
		$base = preg_replace('/[?&]ez_brands_hx=[^&]*/', '', $base) ?? $base;
		$base = preg_replace('/[?&]_wpnonce=[^&]*/', '', $base) ?? $base;
		$base = rtrim($base, '?&');

		if ($pageNum <= 1) {
			return rtrim($base, '/');
		}

		if (str_contains($base, '?')) {
			return $base . '&page=' . $pageNum;
		}

		return $base . '?page=' . $pageNum;
	}

	/**
	 * @param array{brand_id:int, slug:string, name:string, href:string, logo:string, initial:string, game_count:int, address:string} $p
	 */
	public function renderBrandCard(array $p): string
	{
		$href = $p['href'];
		$name = $p['name'];
		$logo = $p['logo'];
		$bid = $p['brand_id'];
		$slug = $p['slug'];
		$count = $p['game_count'];
		$addr = $p['address'];
		$init = $p['initial'];

		$targetBlank = '#' !== $href && $href !== ''
			? ' target="_blank" rel="noopener noreferrer"'
			: '';
		$dataBid = $bid > 0 ? ' data-brand-id="' . $bid . '"' : '';
		$dataSlug = $slug !== '' ? ' data-brand-slug="' . $this->escAttr($slug) . '"' : '';

		$logoBlock = '';
		if ($logo !== '') {
			$logoBlock = '<img class="aspect-square w-full rounded-xl object-cover shadow-md transition-shadow duration-300 group-hover:shadow-lg" src="' . $this->escUrl($logo) . '" loading="lazy" alt="' . $this->escAttr($name) . '" />';
		} else {
			$logoBlock = '<div class="flex aspect-square items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 shadow-md">'
				. '<span class="text-lg font-medium text-slate-400">' . $this->escHtml($init !== '' ? $init : '?') . '</span></div>';
		}

		$countBlock = '';
		if ($count > 0) {
			$countBlock = '<span class="flex max-lg:hidden items-center gap-1.5 rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-500">'
				. (string) $count
				. '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" fill="none" aria-hidden="true">'
				. '<path d="M3.55248 5.52134C3.55248 4.71176 3.42316 3.46277 3.92084 2.73396C5.02103 1.12537 7.35366 1.33882 8.1766 2.90895C8.58023 3.68007 8.42838 4.75791 8.447 5.52134M3.55248 5.52134C2.28182 5.52134 2.02221 6.23477 1.82823 6.79533C1.64894 7.42511 1.46574 8.92985 1.74593 10.5788C1.95559 11.6288 2.77363 12.0903 3.47704 12.149C4.15009 12.2047 6.99118 12.1836 7.81314 12.1836C9.08771 12.1836 9.88322 11.9086 10.2575 10.6481C10.4367 9.66828 10.4857 7.91547 10.1869 6.79533C9.79113 5.67518 8.9917 5.52134 8.447 5.52134M3.55248 5.52134C4.89857 5.46846 7.67696 5.47904 8.447 5.52134" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
				. '<path d="M6 7.71875V9.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'
				. '</svg></span>';
		}

		$addrBlock = '';
		if ($addr !== '') {
			$addrBlock = '<div class="flex items-center gap-1 text-xs text-slate-500">'
				. '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">'
				. '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />'
				. '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'
				. '<span class="line-clamp-1">' . $this->escHtml($addr) . '</span></div>';
		}

		return '<div class="min-w-0"><a class="ez-brand-card group block rounded-xl no-underline text-inherit outline-none ring-primary-600 transition-transform duration-300 ease-out focus-visible:ring-2 focus-visible:ring-offset-2"'
			. ' href="' . $this->escUrl($href) . '"'
			. $targetBlank
			. $dataBid
			. $dataSlug
			. '>'
			. '<div class="flex flex-col gap-5 max-lg:gap-4 pt-0.5 transition-transform duration-300 ease-out group-hover:scale-105">'
			. '<div class="relative block">' . $logoBlock . '</div>'
			. '<div class="flex flex-col gap-1.5 pt-3">'
			. '<div class="flex items-center justify-between">'
			. '<span class="line-clamp-1 text-sm font-semibold text-slate-800">' . $this->escHtml($name) . '</span>'
			. $countBlock
			. '</div>'
			. $addrBlock
			. '</div></div></a></div>';
	}

	public function ajaxFragmentUrl(string $home, int $page): string
	{
		$path = '/ajax?action=brands.fragment&page=' . max(1, $page);

		return $home !== '' ? $home . $path : $path;
	}

	public function renderPaginationNav(int $currentPage, int $totalPages, string $listingBase, string $home): string
	{
		if ($totalPages <= 1) {
			return '';
		}

		$currentPage = max(1, min($totalPages, $currentPage));
		$ind = ' hx-indicator="#ez-brands-htmx-skeleton"';

		$prev = '';
		if ($currentPage > 1) {
			$p = $currentPage - 1;
			$prevUrl = $this->relativeOrFullPageUrl($listingBase, $p);
			$prev = '<a class="ez-brands-pagination__arrow ez-brands-pagination__arrow-prev inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-navyBlue transition hover:border-primary-500 hover:text-primary-600"'
				. ' href="' . $this->escUrl($prevUrl) . '"'
				. ' hx-get="' . $this->escUrl($this->ajaxFragmentUrl($home, $p)) . '"'
				. ' hx-target="#brands-directory-swap" hx-swap="outerHTML"' . $ind
				. ' aria-label="یک صفحه قبل" title="یک صفحه قبل">'
				. '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180" aria-hidden="true"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>'
				. '</a>';
		}

		$next = '';
		if ($currentPage < $totalPages) {
			$n = $currentPage + 1;
			$nextUrl = $this->relativeOrFullPageUrl($listingBase, $n);
			$next = '<a class="ez-brands-pagination__arrow ez-brands-pagination__arrow-next inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-navyBlue transition hover:border-primary-500 hover:text-primary-600"'
				. ' href="' . $this->escUrl($nextUrl) . '"'
				. ' hx-get="' . $this->escUrl($this->ajaxFragmentUrl($home, $n)) . '"'
				. ' hx-target="#brands-directory-swap" hx-swap="outerHTML"' . $ind
				. ' aria-label="یک صفحه بعد" title="یک صفحه بعد">'
				. '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>'
				. '</a>';
		}

		$slots = '';
		foreach ($this->paginationNumbers($currentPage, $totalPages) as $slot) {
			if ($slot === null) {
				$slots .= '<span class="shrink-0 px-1 text-slate-400" aria-hidden="true">…</span>';
				continue;
			}
			$i = (int) $slot;
			$pageUrl = $this->relativeOrFullPageUrl($listingBase, $i);
			$isCurrent = ($i === $currentPage);
			$currentClasses = 'min-w-9 shrink-0 rounded-lg bg-primary-600 px-2.5 py-1.5 text-center text-sm font-bold text-white shadow-md shadow-primary-600/25 pointer-events-none';
			$linkClasses = 'min-w-9 shrink-0 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-center text-sm font-medium text-navyBlue transition hover:border-primary-500 hover:text-primary-600';

			if ($isCurrent) {
				$t = 'صفحه ' . $i . ' (فعلی)';
				$slots .= '<span class="' . $this->escAttr($currentClasses) . '" aria-current="page" title="' . $this->escAttr($t) . '">' . $i . '</span>';
				continue;
			}

			$edgeLabel = '';
			if (1 === $i) {
				$edgeLabel = 'اولین صفحه';
			} elseif ($i === $totalPages) {
				$edgeLabel = 'آخرین صفحه';
			}

			$edgeAttr = '';
			if ($edgeLabel !== '') {
				$edgeAttr = ' aria-label="' . $this->escAttr(trim($edgeLabel)) . '" title="' . $this->escAttr(trim($edgeLabel)) . '"';
			}

			$slots .= '<a class="' . $this->escAttr($linkClasses) . '" href="' . $this->escUrl($pageUrl) . '"'
				. ' hx-get="' . $this->escUrl($this->ajaxFragmentUrl($home, $i)) . '"'
				. ' hx-target="#brands-directory-swap" hx-swap="outerHTML"' . $ind . $edgeAttr
				. '>' . $i . '</a>';
		}

		return '<nav class="mt-10 flex w-full max-w-full flex-nowrap items-center justify-center gap-1.5 overflow-x-auto border-t border-slate-100 pt-8 [scrollbar-width:thin]"'
			. ' aria-label="صفحه‌بندی برندها">'
			. $prev
			. '<div class="flex min-w-0 shrink flex-nowrap items-center justify-center gap-1.5">' . $slots . '</div>'
			. $next
			. '</nav>';
	}

	public function relativeOrFullPageUrl(string $listingBase, int $pageNum): string
	{
		return $this->buildPushUrlForPage($listingBase, $pageNum);
	}

	/**
	 * @return array<int|null>
	 */
	public function paginationNumbers(int $current, int $total): array
	{
		$total = max(1, $total);
		$current = max(1, min($total, $current));

		if ($total <= 7) {
			return range(1, $total);
		}

		$include = [1, $total, $current];
		foreach ([$current - 1, $current + 1] as $neighbor) {
			if ($neighbor >= 2 && $neighbor <= $total - 1) {
				$include[] = $neighbor;
			}
		}

		$include = array_values(array_unique(array_filter($include, static fn($n) => (int) $n >= 1 && (int) $n <= $total)));
		sort($include, SORT_NUMERIC);

		$out = [];
		$prev = 0;
		foreach ($include as $p) {
			if ($prev && $p - $prev > 1) {
				$out[] = null;
			}
			$out[] = $p;
			$prev = $p;
		}

		return $out;
	}



	public function taxonomyMissingWrap(string $msg): string
	{
		return '<div id="brands-directory-swap" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">'
			. $this->escHtml($msg) . '</div>';
	}

	public function htmlErrorBody(string $msg): string
	{
		return '<div id="brands-directory-swap" class="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-900">'
			. $this->escHtml($msg) . '</div>';
	}

	public function escAttr(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	public function escHtml(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	public function escUrl(string $u): string
	{
		$u = trim($u);
		if ($u === '' || str_starts_with($u, '#')) {
			return $this->escAttr($u);
		}
		if (preg_match('#^(https?:|/|\.|\?)#i', $u)) {
			return $this->escAttr($u);
		}

		return $this->escAttr($u);
	}
}
