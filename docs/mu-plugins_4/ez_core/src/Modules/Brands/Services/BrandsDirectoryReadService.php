<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\Services;

use EscapeZoom\Core\Core\Bootstrap;
use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Database\WpTableNames;
use EscapeZoom\Core\Modules\WpCore\Models\Term;
use EscapeZoom\Core\Modules\WpCore\Models\TermMeta;
use EscapeZoom\Core\Modules\WpCore\Models\TermTaxonomy;
use Illuminate\Database\Capsule\Manager as Capsule;
/**
 * Brands directory read model + HTML assembly for gateway `brands.fragment` (no full WP load).
 */
final class BrandsDirectoryReadService
{
	private const TERMS_PER_PAGE = 24;

	private const ORDER_MODE = 'popular';

	private const TAXONOMY = 'product_brand';

	private const ARCHIVE_SLUG = 'brands';

	private const META_THUMBNAIL = 'thumbnail_id';

	private const META_ADDRESS = 'brand_address';

	private BrandsDirectoryHtmlRenderer $renderer;

	public function __construct(?BrandsDirectoryHtmlRenderer $renderer = null)
	{
		$this->renderer = $renderer ?? new BrandsDirectoryHtmlRenderer();
	}

	public function countProductBrandTerms(): int
	{
		$this->ensureCapsule();

		return TermTaxonomy::query()
			->where('taxonomy', self::TAXONOMY)
			->count();
	}

	public function buildFragment(int $page): BrandsDirectoryFragmentResult
	{
		$page = max(1, $page);

		if (! $this->capsuleReady()) {
			return new BrandsDirectoryFragmentResult(
				$this->renderer->htmlErrorBody('Brands fragment: data layer unavailable.'),
				500
			);
		}

		try {
			$taxonomyCount = $this->countProductBrandTerms();
			if ($taxonomyCount < 1) {
				return new BrandsDirectoryFragmentResult(
					$this->renderer->taxonomyMissingWrap($this->taxonomyMissingMessage()),
					200
				);
			}

			$rows = $this->fetchTermPage($page, self::TERMS_PER_PAGE, self::ORDER_MODE);
			$termIds = [];
			foreach ($rows as $r) {
				$termIds[] = (int) ($r['term_id'] ?? 0);
			}

			$metaByTerm = $this->batchTermMeta($termIds);
			$thumbIds = [];
			foreach ($metaByTerm as $m) {
				$thumb = isset($m[self::META_THUMBNAIL]) ? (int) $m[self::META_THUMBNAIL] : 0;
				if ($thumb > 0) {
					$thumbIds[] = $thumb;
				}
			}
			$thumbUrls = $this->batchAttachmentLargeSrcs(array_values(array_unique($thumbIds)));

			$home = $this->optionHomeUrl();
			$pretty = $this->isPrettyPermalinks();
			$listingBase = $this->resolveListingBaseUrl($home, $pretty);

			$cardsHtml = '';
			foreach ($rows as $r) {
				$termId = (int) ($r['term_id'] ?? 0);
				$slug = (string) ($r['slug'] ?? '');
				$name = (string) ($r['name'] ?? '');
				$count = (int) ($r['count'] ?? 0);
				$m = $metaByTerm[$termId] ?? [];
				$imgId = isset($m[self::META_THUMBNAIL]) ? (int) $m[self::META_THUMBNAIL] : 0;
				$logo = $imgId > 0 ? (string) ($thumbUrls[$imgId] ?? '') : '';
				$address = isset($m[self::META_ADDRESS]) ? (string) $m[self::META_ADDRESS] : '';
				$href = $this->termPublicUrl($home, $pretty, $slug);
				$initial = $this->firstChar($name);
				$cardsHtml .= $this->renderer->renderBrandCard(
					[
						'brand_id' => $termId,
						'slug' => $slug,
						'name' => $name,
						'href' => $href,
						'logo' => $logo,
						'initial' => $initial,
						'game_count' => $count,
						'address' => $address,
					]
				);
			}

			$totalPages = self::TERMS_PER_PAGE > 0
				? (int) max(1, (int) ceil($taxonomyCount / self::TERMS_PER_PAGE))
				: 1;

			$nav = $this->renderer->renderPaginationNav(
				$page,
				$totalPages,
				$listingBase,
				$home
			);

			$html = '<div id="brands-directory-swap" class="relative">'
				. '<div class="relative w-full grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-x-8 gap-y-10">'
				. $cardsHtml
				. '</div>';

			if ($rows === []) {
				$html .= '<p class="mt-12 text-center text-slate-500">هنوز برندی ثبت نشده است.</p>';
			}

			$html .= $nav . '</div>';

			$push = $this->renderer->buildPushUrlForPage($listingBase, $page);
			$pushClean = null;
			if ($push !== '') {
				$clean = preg_replace('/[\x00-\x1F\x7F]/', '', $push);
				if (is_string($clean) && $clean !== '') {
					$pushClean = $clean;
				}
			}

			return new BrandsDirectoryFragmentResult($html, 200, $pushClean, true);
		} catch (\Throwable $e) {
			if (function_exists('error_log')) {
				error_log(
					sprintf(
						'[%s] %s @ %s:%d',
						$e::class,
						$e->getMessage(),
						$e->getFile(),
						$e->getLine()
					)
				);
				error_log('[EZ BrandsDirectoryReadService] ' . $e->getTraceAsString());
			}

			$failMsg = 'خطا در بارگذاری لیست برندها.';
			if (defined('WP_DEBUG') && WP_DEBUG) {
				$failMsg .= ' [' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ']';
			}

			return new BrandsDirectoryFragmentResult(
				$this->renderer->htmlErrorBody($failMsg),
				500
			);
		}
	}

	private function capsuleReady(): bool
	{
		return class_exists(Capsule::class) && CapsuleBoot::isBooted();
	}

	private function ensureCapsule(): void
	{
		if (! $this->capsuleReady()) {
			Bootstrap::bootDataLayerOnly();
		}
		if (! $this->capsuleReady()) {
			throw new \RuntimeException('Eloquent data layer is not booted for brands directory.');
		}
	}

	/**
	 * @return list<array{term_id:int, slug:string, name:string, count:int}>
	 */
	private function fetchTermPage(int $page, int $perPage, string $orderMode): array
	{
		$page = max(1, $page);
		$perPage = max(1, min(500, $perPage));
		$offset = $perPage * ($page - 1);

		$ttTbl = WpTableNames::termTaxonomy();
		$tTbl = WpTableNames::terms();

		$query = TermTaxonomy::query()
			->from($ttTbl . ' as tt')
			->join($tTbl . ' as t', 't.term_id', '=', 'tt.term_id')
			->where('tt.taxonomy', self::TAXONOMY)
			->select([
				'tt.term_id',
				't.slug',
				't.name',
				'tt.count as ez_term_count',
			]);

		if ('new' === $orderMode) {
			$query->orderByDesc('tt.term_id');
		} else {
			$query->orderByDesc('tt.count')->orderByDesc('tt.term_id');
		}

		$rows = $query->offset($offset)->limit($perPage)->get();
		$out = [];

		foreach ($rows as $row) {
			$out[] = [
				'term_id' => (int) ($row->term_id ?? 0),
				'slug' => (string) ($row->slug ?? ''),
				'name' => (string) ($row->name ?? ''),
				'count' => (int) ($row->ez_term_count ?? $row->count ?? 0),
			];
		}

		return $out;
	}

	/**
	 * @param list<int> $termIds
	 *
	 * @return array<int, array<string, string>>
	 */
	private function batchTermMeta(array $termIds): array
	{
		$termIds = array_values(
			array_unique(
				array_filter(
					array_map('intval', $termIds),
					static fn(int $id): bool => $id > 0
				)
			)
		);

		if ($termIds === []) {
			return [];
		}

		$rows = TermMeta::query()
			->whereIn('term_id', $termIds)
			->whereIn('meta_key', [self::META_THUMBNAIL, self::META_ADDRESS])
			->get(['term_id', 'meta_key', 'meta_value']);

		$map = [];
		foreach ($termIds as $tid) {
			$map[$tid] = [];
		}

		foreach ($rows as $row) {
			$tid = (int) $row->term_id;
			$key = (string) $row->meta_key;
			if (! isset($map[$tid])) {
				$map[$tid] = [];
			}
			$map[$tid][$key] = (string) $row->meta_value;
		}

		return $map;
	}

	/**
	 * @param list<int> $attachIds
	 *
	 * @return array<int, string>
	 */
	private function batchAttachmentLargeSrcs(array $attachIds): array
	{
		$attachIds = array_values(
			array_unique(
				array_filter(
					array_map('intval', $attachIds),
					static fn(int $id): bool => $id > 0
				)
			)
		);

		if ($attachIds === []) {
			return [];
		}

		$pmTbl = WpTableNames::postMeta();
		$postTbl = WpTableNames::posts();

		$rows = Capsule::connection(CapsuleBoot::CONNECTION_WP)
			->table($pmTbl . ' as pm')
			->join($postTbl . ' as p', 'p.ID', '=', 'pm.post_id')
			->where('pm.meta_key', '_wp_attachment_metadata')
			->whereIn('pm.post_id', $attachIds)
			->get(['pm.post_id', 'pm.meta_value', 'p.guid']);

		$uploadBase = $this->uploadsBaseUrl();
		$urls = [];

		foreach ($rows as $row) {
			$id = (int) ($row->post_id ?? 0);
			$raw = (string) ($row->meta_value ?? '');
			$guid = (string) ($row->guid ?? '');
			$parsed = $this->maybeUnserializeMeta($raw);
			$url = $this->pickLargeSrcFromAttachmentMeta($parsed, $uploadBase);
			if ($url === '' && $guid !== '' && str_starts_with($guid, 'http')) {
				$url = $guid;
			}
			if ($url !== '') {
				$urls[$id] = $url;
			}
		}

		return $urls;
	}

	private function maybeUnserializeMeta(string $raw): mixed
	{
		if ($raw === '') {
			return null;
		}
		$m = @unserialize($raw, ['allowed_classes' => false]); // phpcs:ignore Generic.PHP.NoSilencedErrors
		return $m !== false || $raw === serialize(false) ? $m : null;
	}

	private function pickLargeSrcFromAttachmentMeta(mixed $meta, string $uploadBase): string
	{
		if (! is_array($meta)) {
			return '';
		}
		$file = isset($meta['file']) && is_string($meta['file']) ? $meta['file'] : '';
		if ($file === '') {
			return '';
		}
		$baseTrail = rtrim($uploadBase, '/') . '/';
		if (isset($meta['sizes']['large']['file']) && is_string($meta['sizes']['large']['file'])) {
			$sub = dirname($file);
			$suffix = $meta['sizes']['large']['file'];
			$rel = ('.' !== $sub && '/' !== $sub) ? $sub . '/' . $suffix : $suffix;

			return $baseTrail . str_replace(' ', '%20', $rel);
		}

		return $baseTrail . str_replace(' ', '%20', $file);
	}

	private function uploadsBaseUrl(): string
	{
		$home = rtrim($this->optionHomeUrl(), '/');
		$custom = $this->optionString('upload_url_path');
		if ($custom !== '') {
			if (str_starts_with($custom, 'http://') || str_starts_with($custom, 'https://')) {
				return rtrim($custom, '/');
			}

			return $home . '/' . ltrim($custom, '/');
		}

		return $home . '/wp-content/uploads';
	}

	private function optionString(string $name): string
	{
		$value = Capsule::connection(CapsuleBoot::CONNECTION_WP)
			->table(WpTableNames::options())
			->where('option_name', $name)
			->value('option_value');

		return is_scalar($value) ? (string) $value : '';
	}

	private function optionHomeUrl(): string
	{
		$h = $this->optionString('home');
		if ($h !== '') {
			return rtrim($h, '/');
		}
		$s = $this->optionString('siteurl');

		return $s !== '' ? rtrim($s, '/') : '';
	}

	private function isPrettyPermalinks(): bool
	{
		return $this->optionString('permalink_structure') !== '';
	}

	private function termPublicUrl(string $home, bool $pretty, string $slug): string
	{
		$slug = trim($slug);
		if ($slug === '') {
			return '#';
		}

		if ($pretty) {
			return rtrim($home, '/') . '/' . rawurlencode(self::ARCHIVE_SLUG) . '/' . rawurlencode($slug) . '/';
		}

		return rtrim($home, '/') . '/?product_brand=' . rawurlencode($slug);
	}

	private function resolveListingBaseUrl(string $home, bool $pretty): string
	{
		if (! is_string($home) || $home === '') {
			return '/brands';
		}

		$pageId = $this->discoverBrandsDirectoryPageId();
		if ($pageId < 1) {
			return $this->guessBrandsPathFromRewrite($pretty, $home);
		}

		return $this->pagePermalinkSansQuery($home, $pretty, $pageId)
			?: $this->guessBrandsPathFromRewrite($pretty, $home);
	}

	private function guessBrandsPathFromRewrite(bool $pretty, string $home): string
	{
		if ($pretty) {
			return rtrim($home, '/') . '/' . self::ARCHIVE_SLUG . '/';
		}

		return rtrim($home, '/') . '/?pagename=' . rawurlencode(self::ARCHIVE_SLUG);
	}

	private function discoverBrandsDirectoryPageId(): int
	{
		$pmTbl = WpTableNames::postMeta();
		$poTbl = WpTableNames::posts();

		$id = Capsule::connection(CapsuleBoot::CONNECTION_WP)
			->table($pmTbl . ' as pm')
			->join($poTbl . ' as po', 'po.ID', '=', 'pm.post_id')
			->where('pm.meta_key', '_wp_page_template')
			->where('po.post_type', 'page')
			->where('po.post_status', 'publish')
			->where(function ($query): void {
				$query->where('pm.meta_value', 'page-brands.php')
					->orWhere('pm.meta_value', 'templates/page-brands.php')
					->orWhere('pm.meta_value', 'like', '%page-brands.php');
			})
			->orderBy('po.ID')
			->value('po.ID');

		return (int) ($id ?? 0);
	}

	private function pagePermalinkSansQuery(string $home, bool $pretty, int $pageId): string
	{
		$posts = WpTableNames::posts();

		$hit = Capsule::connection(CapsuleBoot::CONNECTION_WP)
			->table($posts)
			->where('ID', $pageId)
			->where('post_status', 'publish')
			->where('post_type', 'page')
			->first(['post_name', 'post_parent']);

		if ($hit === null) {
			return '';
		}

		if (! $pretty) {
			return rtrim($home, '/') . '/?page_id=' . $pageId;
		}

		$segments = [];
		$walkerId = $pageId;
		$safety = 0;

		while ($walkerId > 0 && $safety++ < 20) {
			$row = Capsule::connection(CapsuleBoot::CONNECTION_WP)
				->table($posts)
				->where('ID', $walkerId)
				->first(['post_name', 'post_parent']);

			if ($row === null) {
				break;
			}

			array_unshift($segments, (string) ($row->post_name ?? ''));
			$walkerId = (int) ($row->post_parent ?? 0);
		}

		$path = implode('/', array_map('rawurlencode', $segments));

		return $path !== '' ? rtrim($home, '/') . '/' . $path . '/' : '';
	}

	private function firstChar(string $name): string
	{
		if ($name === '') {
			return '?';
		}
		if (function_exists('mb_substr')) {
			$c = mb_substr($name, 0, 1, 'UTF-8');
			return $c !== '' ? $c : '?';
		}
		return substr($name, 0, 1) ?: '?';
	}

	private function taxonomyMissingMessage(): string
	{
		return 'تاکسونومی برند محصول (WooCommerce) فعال نیست.';
	}
}
