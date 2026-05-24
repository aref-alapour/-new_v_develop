<?php

namespace EscapeZoom\Core\Modules\Common\Services;

use EscapeZoom\Core\Modules\Common\Models\ProductSnapshot;

class ProductSnapshotService
{
    /**
     * Prefixes that may be prepended to city labels in legacy data.
     *
     * @var string[]
     */
    private array $gameTypeCityPrefixes = [
        'اتاق فرار',
        'سینما ترس',
        'لیزرتگ',
        'اتاق خشم',
        'کافه بازی',
        'پینت بال',
        'فوتبال حبابی',
    ];

    public function buildSnapshotRow(int $productId): array|false
    {
        if ($productId <= 0 || get_post_type($productId) !== 'product') {
            return false;
        }

        if (get_post_status($productId) !== 'publish') {
            return false;
        }

        $saleStatus = (string) $this->getPostMetaDirect($productId, 'product_state', '');
        if ($saleStatus === '') {
            $saleStatus = 'active';
        }

        $productType = null;
        $cityData = null;
        $identityTerms = get_the_terms($productId, 'ez_game_identity');
        if ($identityTerms && !is_wp_error($identityTerms) && $identityTerms !== []) {
            $idTerm = $identityTerms[0];
            if ($idTerm) {
                $productType = $idTerm->name;
            }
        }
        $terms = get_the_terms($productId, 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if ((int) $term->parent !== 0) {
                    $parentTerm = get_term($term->parent);
                    if ($parentTerm && !is_wp_error($parentTerm) && $productType === null) {
                        $productType = $parentTerm->name;
                    }
                    $cityData = [
                        'id' => (int) $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                    ];
                } elseif ($productType === null) {
                    $productType = $term->name;
                }
            }
        }

        if (!$productType) {
            $productType = 'نامشخص';
        }

        $cityData = $this->normalizeCityData($cityData, $productType);

        $productHood = (string) $this->getPostMetaDirect($productId, 'room_loc', '');

        $areaData = null;
        $tagsData = [];
        $seenTagKeys = [];

        $appendTagRow = static function (string $title, string $url) use (&$tagsData, &$seenTagKeys): void {
            $k = $title . "\0" . $url;
            if (isset($seenTagKeys[$k])) {
                return;
            }
            $seenTagKeys[$k] = true;
            $tagsData[] = [
                'title' => $title,
                'url' => $url,
            ];
        };

        $genreTerms = get_the_terms($productId, 'product_genre');
        if ($genreTerms && !is_wp_error($genreTerms)) {
            foreach ($genreTerms as $tag) {
                $termLink = get_term_link($tag->term_id);
                $termUrl = is_wp_error($termLink) ? '' : str_replace(home_url(), '', (string) $termLink);
                $appendTagRow($tag->name, $termUrl);
            }
        }

        $tagTerms = get_the_terms($productId, 'product_tag');
        if ($tagTerms && !is_wp_error($tagTerms)) {
            foreach ($tagTerms as $tag) {
                $termLink = get_term_link($tag->term_id);
                $termUrl = is_wp_error($termLink) ? '' : str_replace(home_url(), '', (string) $termLink);

                if (strpos($tag->name, '|||||') === 0) {
                    $appendTagRow(str_replace('|||||', '', $tag->name), $termUrl);
                    continue;
                }

                if ($areaData === null) {
                    $areaData = [
                        'title' => $tag->name,
                        'url' => $termUrl,
                    ];
                }

                $appendTagRow($tag->name, $termUrl);
            }
        }

        $brandData = null;
        $brandTerms = get_the_terms($productId, 'yith_product_brand');
        if ($brandTerms && !is_wp_error($brandTerms)) {
            $brand = $brandTerms[0];
            $brandThumbnailId = get_term_meta($brand->term_id, 'thumbnail_id', true);
            $brandImage = $brandThumbnailId ? wp_get_attachment_url($brandThumbnailId) : '';
            $brandData = [
                'id' => (int) $brand->term_id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'image' => $brandImage ?: '',
            ];
        }

        $productName = (string) get_the_title($productId);
        $fullUrl = get_permalink($productId);
        $relativeUrl = $fullUrl ? str_replace(home_url(), '', $fullUrl) : '';
        $imageUrl = wp_get_attachment_url(get_post_thumbnail_id($productId));

        $schedule = [];
        if (function_exists('get_sanses')) {
            $schedule = get_sanses($productId);
        } else {
            $metaSchedule = $this->getPostMetaDirect($productId, 'sanses', []);
            if (is_array($metaSchedule)) {
                $schedule = $metaSchedule;
            }
        }

        $minPrice = $this->extractMinPrice($schedule, 0);

        $managerId = (int) $this->getPostMetaDirect($productId, 'sans_manager', 0);
        if ($managerId <= 0) {
            $managerId = (int) $this->getPostMetaDirect($productId, 'manager_id', 0);
        }

        return [
            'product_id' => $productId,
            'product_name' => $productName,
            'product_type' => $productType,
            'product_status' => $saleStatus,
            'product_url' => $relativeUrl,
            'product_image_url' => $imageUrl ?: '',
            'min_price' => max(0, $minPrice),
            'min_prepayment_person_count' => max(1, (int) $this->getPostMetaDirect($productId, 'pish_pardakht_per_person', 1)),
            'discount_data' => null,
            'product_hood' => $productHood,
            'product_brand' => $brandData ? wp_json_encode($brandData, JSON_UNESCAPED_UNICODE) : null,
            'product_city' => $cityData ? wp_json_encode($cityData, JSON_UNESCAPED_UNICODE) : null,
            'product_area' => $areaData ? wp_json_encode($areaData, JSON_UNESCAPED_UNICODE) : null,
            'product_tags' => !empty($tagsData) ? wp_json_encode($tagsData, JSON_UNESCAPED_UNICODE) : null,
            'comments_count' => max(0, (int) $this->getPostMetaDirect($productId, 'comments_count_new', 0)),
            'rate' => max(0, min(5, (float) $this->getPostMetaDirect($productId, 'ez_weighted_rating_overall', 0))),
            'schedule' => !empty($schedule) ? wp_json_encode($schedule, JSON_UNESCAPED_UNICODE) : null,
            'owner_id' => (int) get_post_field('post_author', $productId),
            'manager_id' => $managerId,
            'rank_popular' => 0,
            'rank_hottest' => 0,
            'rank_topsale' => 0,
        ];
    }

    public function upsertSnapshot(int $productId): bool
    {
        $row = $this->buildSnapshotRow($productId);
        if ($row === false) {
            return false;
        }

        try {
            ProductSnapshot::query()->updateOrCreate(['product_id' => $productId], $row);
        } catch (\Throwable $e) {
            // Fallback path when Eloquent connection is not available in runtime.
            global $wpdb;
            $wpdb->replace('wp_products_snapshot', $row);
        }
        return true;
    }

    public function deleteSnapshot(int $productId): bool
    {
        try {
            ProductSnapshot::query()->where('product_id', $productId)->delete();
        } catch (\Throwable $e) {
            global $wpdb;
            $wpdb->delete('wp_products_snapshot', ['product_id' => $productId], ['%d']);
        }
        return true;
    }

    public function backfillAllProducts(int $batch = 50): array
    {
        $batch = max(5, min(200, $batch));
        $paged = 1;
        $total = 0;
        $success = 0;
        $failed = 0;
        $lastProductId = 0;
        $errors = [];

        do {
            $query = new \WP_Query([
                'post_type' => 'product',
                'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
                'posts_per_page' => $batch,
                'paged' => $paged,
                'fields' => 'ids',
                'no_found_rows' => true,
                'orderby' => 'ID',
                'order' => 'ASC',
            ]);

            $ids = $query->posts;
            if (empty($ids)) {
                break;
            }

            foreach ($ids as $productId) {
                $lastProductId = (int) $productId;
                $total++;
                try {
                    if ($this->upsertSnapshot((int) $productId)) {
                        $success++;
                    } else {
                        $failed++;
                        if (count($errors) < 20) {
                            $errors[] = 'Product ' . (int) $productId . ': skipped (not publish or invalid).';
                        }
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    if (count($errors) < 20) {
                        $errors[] = 'Product ' . (int) $productId . ': ' . $e->getMessage();
                    }
                }
            }

            $paged++;
            wp_reset_postdata();
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        } while (true);

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'last_product_id' => $lastProductId,
            'errors' => $errors,
        ];
    }

    public function getActiveSearchRows(): array
    {
        $rows = ProductSnapshot::query()
            ->select([
                'product_id',
                'product_type',
                'product_name',
                'product_status',
                'product_url',
                'product_image_url',
                'product_brand',
                'product_hood',
                'product_city',
                'product_tags',
            ])
            ->where('product_status', 'active')
            ->get();

        $data = [];
        foreach ($rows as $row) {
            $data[] = method_exists($row, 'getAttributes') ? $row->getAttributes() : (array) $row;
        }
        return $data;
    }

    public function searchQuery(string $rawQuery, int $limit = 20, string $requestId = ''): array
    {
        $t0 = microtime(true);
        $requestId = trim($requestId);
        if ($requestId === '') {
            $requestId = 'ezs_local';
        }
        $query = $this->normalizePersian($rawQuery);
        if (mb_strlen($query) < 2) {
            return [
                'status' => 'empty',
                'message' => 'لطفا حداقل 2 حرف وارد کنید.',
                'data' => [],
                'request_id' => $requestId,
            ];
        }

        $cacheKey = 'ez_snapshot_search_' . md5($query . '|' . $limit);
        $transientKey = 'ez_snapshot_search_' . md5($query . '|' . $limit);
        $cached = wp_cache_get($cacheKey, 'ez_search');
        if (is_array($cached)) {
            error_log('[EZ_SEARCH] searchQuery request_id=' . $requestId . ' cache=memory hit q="' . $query . '"');
            return $cached;
        }
        $cachedTransient = get_transient($transientKey);
        if (is_array($cachedTransient)) {
            wp_cache_set($cacheKey, $cachedTransient, 'ez_search', 60);
            error_log('[EZ_SEARCH] searchQuery request_id=' . $requestId . ' cache=transient hit q="' . $query . '"');
            return $cachedTransient;
        }

        $tCandidatesStart = microtime(true);
        $tokens = array_values(array_filter(explode(' ', $query)));
        $responseCacheTtl = mb_strlen($query) <= 2 ? 20 : 90;
        $candidateCacheTtl = mb_strlen($query) <= 2 ? 20 : 45;
        $products = $this->getSearchCandidates($tokens, 140, $candidateCacheTtl);
        $tCandidatesMs = (int) round((microtime(true) - $tCandidatesStart) * 1000);
        $results = [];

        $tScoringStart = microtime(true);
        foreach ($products as $p) {
            $brand = json_decode((string) ($p['product_brand'] ?? ''), true) ?: [];
            $city  = json_decode((string) ($p['product_city'] ?? ''), true) ?: [];
            $tags  = json_decode((string) ($p['product_tags'] ?? ''), true) ?: [];

            $brandName   = $brand['name'] ?? '';
            $brandSlug   = $brand['slug'] ?? '';
            $cityName    = $city['name'] ?? '';
            $citySlug    = $city['slug'] ?? '';
            $typeName    = (string) ($p['product_type'] ?? '');
            $hoodName    = (string) ($p['product_hood'] ?? '');
            $productName = (string) ($p['product_name'] ?? '');
            $brandNameN = $this->normalizePersian($brandName);
            $cityNameN = $this->normalizePersian($cityName);
            $typeNameN = $this->normalizePersian($typeName);
            $hoodNameN = $this->normalizePersian($hoodName);
            $productNameN = $this->normalizePersian($productName);
            $normalizedTags = [];
            foreach ($tags as $tag) {
                $tagTitleRaw = (string) ($tag['title'] ?? '');
                if ($tagTitleRaw !== '') {
                    $normalizedTags[] = $this->normalizePersian($tagTitleRaw);
                }
            }

            $isCompoundMatch = false;
            $cityMatched = false;
            $typeMatched = false;

            foreach ($tokens as $token) {
                if (mb_strlen($token) >= 2) {
                    if (mb_strpos($cityNameN, $token) !== false) {
                        $cityMatched = true;
                    }
                    if (mb_strpos($typeNameN, $token) !== false) {
                        $typeMatched = true;
                    }
                }
            }
            if ($cityMatched && $typeMatched && count($tokens) >= 2) {
                $isCompoundMatch = true;
            }

            $multiWordProductScore = 0;
            $isMultiWordMatch = false;

            if (count($tokens) > 1) {
                $matchedAllTokens = true;
                foreach ($tokens as $token) {
                    if (mb_strlen($token) < 2) {
                        continue;
                    }

                    $tokenMatched = false;
                    $tokenMaxScore = 0;

                    if (mb_strpos($productNameN, $token) !== false) {
                        $tokenMaxScore = max($tokenMaxScore, 40);
                        $tokenMatched = true;
                    }
                    if (mb_strpos($cityNameN, $token) !== false) {
                        $tokenMaxScore = max($tokenMaxScore, 30);
                        $tokenMatched = true;
                    }
                    if (mb_strpos($hoodNameN, $token) !== false) {
                        $tokenMaxScore = max($tokenMaxScore, 20);
                        $tokenMatched = true;
                    }
                    if (mb_strpos($typeNameN, $token) !== false) {
                        $tokenMaxScore = max($tokenMaxScore, 60);
                        $tokenMatched = true;
                    }
                    if (mb_strpos($brandNameN, $token) !== false) {
                        $tokenMaxScore = max($tokenMaxScore, 10);
                        $tokenMatched = true;
                    }

                    foreach ($normalizedTags as $normalizedTag) {
                        if (mb_strpos($normalizedTag, $token) !== false) {
                            $tokenMaxScore = max($tokenMaxScore, 70);
                            $tokenMatched = true;
                        }
                    }

                    if (!$tokenMatched) {
                        $matchedAllTokens = false;
                        break;
                    }
                    $multiWordProductScore += $tokenMaxScore;
                }

                if ($matchedAllTokens) {
                    $isMultiWordMatch = true;
                }
            }

            foreach ($tags as $tag) {
                $tagTitle = (string) ($tag['title'] ?? '');
                $score = $this->calcScore($tagTitle, $query, 70);
                if ($score > 0) {
                    $results['tag_' . ($tag['url'] ?? $tagTitle)] = [
                        'type' => 'tag',
                        'title' => 'بازی‌های ' . $tagTitle,
                        'url' => (string) ($tag['url'] ?? ''),
                        'score' => $score,
                        'ui' => 'link',
                    ];
                }
            }

            $scoreType = $this->calcScore($typeName, $query, 60);
            if ($scoreType > 0) {
                $results['type_' . $typeName] = [
                    'type' => 'game_type',
                    'title' => $typeName,
                    'url' => '/city/' . urlencode($typeName),
                    'score' => $scoreType,
                    'ui' => 'link',
                ];
            }

            if ($isCompoundMatch) {
                $results['city_type_' . $cityName . '_' . $typeName] = [
                    'type' => 'city_type',
                    'title' => $cityName,
                    'url' => '/city/' . $citySlug,
                    'score' => 75,
                    'ui' => 'link',
                ];
            }

            $scoreName = $this->calcScore($productName, $query, 40);
            $scoreHood = $this->calcScore($hoodName, $query, 20);
            $finalProductScore = max($scoreName, $scoreHood, $isMultiWordMatch ? $multiWordProductScore : 0);
            if ($isCompoundMatch && $finalProductScore === 0.0) {
                $finalProductScore = 48;
            }

            if ($finalProductScore > 0) {
                $pid = (int) ($p['product_id'] ?? 0);
                $key = 'prod_' . $pid;
                if (!isset($results[$key]) || ($results[$key]['score'] ?? 0) < $finalProductScore) {
                    $results[$key] = [
                        'type' => 'product',
                        'title' => $productName,
                        'image' => $this->sanitizePublicImageUrl((string) ($p['product_image_url'] ?? '')),
                        'url' => (string) ($p['product_url'] ?? ''),
                        'hood' => $hoodName,
                        'city' => $cityName,
                        'brand' => $brandName,
                        'product_type' => $typeName,
                        'score' => $finalProductScore,
                        'ui' => 'card',
                    ];
                }
            }

            $scoreCity = $this->calcScore($cityName, $query, 30);
            if ($scoreCity > 0) {
                $results['city_' . $cityName] = [
                    'type' => 'city',
                    'title' => 'بازی‌های شهر ' . $cityName,
                    'url' => '/city/' . $citySlug,
                    'score' => $scoreCity,
                    'ui' => 'link',
                ];
            }

            $scoreBrand = $this->calcScore($brandName, $query, 10);
            if ($scoreBrand > 0) {
                $results['brand_' . $brandSlug] = [
                    'type' => 'brand',
                    'title' => 'مجموعه: ' . $brandName,
                    'url' => '/blog/product-brands/' . $brandSlug,
                    'score' => $scoreBrand,
                    'ui' => 'link',
                ];
            }
        }
        $tScoringMs = (int) round((microtime(true) - $tScoringStart) * 1000);

        $tSortStart = microtime(true);
        $finalResults = array_values($results);
        usort($finalResults, static fn ($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));
        $finalResults = array_slice($finalResults, 0, max(1, $limit));
        $tSortMs = (int) round((microtime(true) - $tSortStart) * 1000);

        $response = empty($finalResults)
            ? [
                'status' => 'success',
                'has_results' => false,
                'html' => '<div class="no-results-msg" style="padding:20px; text-align:center; color:#888;">هیچ نتیجه‌ای یافت نشد.</div>',
            ]
            : [
                'status' => 'success',
                'has_results' => true,
                'data' => $finalResults,
            ];
        $response['request_id'] = $requestId;

        wp_cache_set($cacheKey, $response, 'ez_search', $responseCacheTtl);
        set_transient($transientKey, $response, $responseCacheTtl);
        $totalMs = (int) round((microtime(true) - $t0) * 1000);
        error_log(
            '[EZ_SEARCH] searchQuery done request_id=' . $requestId . ' q="' . $query .
            '" tokens=' . count($tokens) .
            ' candidates=' . count($products) .
            ' candidates_ms=' . $tCandidatesMs .
            ' scoring_ms=' . $tScoringMs .
            ' sort_ms=' . $tSortMs .
            ' total_ms=' . $totalMs .
            ' response_cache_ttl=' . $responseCacheTtl .
            ' candidate_cache_ttl=' . $candidateCacheTtl
        );
        return $response;
    }

    private function extractMinPrice(array $schedule, int $fallback): int
    {
        $fallback = max(0, $fallback);
        if ($schedule === []) {
            return $fallback;
        }

        $candidates = [];
        foreach ($schedule as $daySanses) {
            if (!is_array($daySanses)) {
                continue;
            }
            foreach ($daySanses as $sans) {
                if (!is_array($sans)) {
                    continue;
                }
                $offPrice = isset($sans['off_price']) ? (int) $sans['off_price'] : 0;
                $price = isset($sans['price']) ? (int) $sans['price'] : 0;
                $value = $offPrice > 0 ? $offPrice : $price;
                if ($value > 0) {
                    $candidates[] = $value;
                }
            }
        }

        return $candidates === [] ? $fallback : min($candidates);
    }

    private function getPostMetaDirect(int $postId, string $metaKey, mixed $default = null): mixed
    {
        global $wpdb;

        $value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC LIMIT 1",
                $postId,
                $metaKey
            )
        );

        if ($value === null) {
            return $default;
        }

        return maybe_unserialize($value);
    }

    private function getSearchCandidates(array $tokens, int $limit = 350, int $cacheTtl = 45): array
    {
        $t0 = microtime(true);
        global $wpdb;

        $cacheKey = 'ez_snapshot_candidates_' . md5(wp_json_encode([$tokens, $limit]));
        $cached = wp_cache_get($cacheKey, 'ez_search');
        if (is_array($cached)) {
            error_log('[EZ_SEARCH] getSearchCandidates cache=memory hit tokens=' . count($tokens) . ' rows=' . count($cached));
            return $cached;
        }
        $transientKey = 'ez_snapshot_candidates_' . md5(wp_json_encode([$tokens, $limit]));
        $cachedTransient = get_transient($transientKey);
        if (is_array($cachedTransient)) {
            wp_cache_set($cacheKey, $cachedTransient, 'ez_search', $cacheTtl);
            error_log('[EZ_SEARCH] getSearchCandidates cache=transient hit tokens=' . count($tokens) . ' rows=' . count($cachedTransient));
            return $cachedTransient;
        }

        $where = ["product_status = 'active'"];
        $params = [];

        // Performance: use only the longest token for SQL candidate narrowing,
        // then do full weighted matching in PHP.
        $mainToken = '';
        foreach ($tokens as $token) {
            $token = trim((string) $token);
            if (mb_strlen($token) < 2) {
                continue;
            }
            if (mb_strlen($token) > mb_strlen($mainToken)) {
                $mainToken = $token;
            }
        }

        if ($mainToken !== '') {
            $like = '%' . $wpdb->esc_like($mainToken) . '%';
            $where[] = "(product_name LIKE %s OR product_type LIKE %s OR product_hood LIKE %s OR product_city LIKE %s OR product_brand LIKE %s OR product_tags LIKE %s)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql = "SELECT product_id, product_type, product_name, product_status, product_url, product_image_url, product_brand, product_hood, product_city, product_tags
                FROM wp_products_snapshot
                WHERE " . implode(' AND ', $where) . "
                ORDER BY comments_count DESC, rate DESC
                LIMIT %d";

        $params[] = max(50, min(1000, $limit));
        $prepared = $wpdb->prepare($sql, ...$params);

        $rows = $wpdb->get_results($prepared, ARRAY_A);
        $result = is_array($rows) ? $rows : [];
        wp_cache_set($cacheKey, $result, 'ez_search', $cacheTtl);
        set_transient($transientKey, $result, $cacheTtl);
        $elapsedMs = (int) round((microtime(true) - $t0) * 1000);
        error_log(
            '[EZ_SEARCH] getSearchCandidates done tokens=' . count($tokens) .
            ' main_token="' . $mainToken . '"' .
            ' rows=' . count($result) .
            ' elapsed_ms=' . $elapsedMs .
            ' cache_ttl=' . $cacheTtl
        );
        return $result;
    }

    private function normalizePersian(string $value): string
    {
        $value = str_replace(['ي', 'ك', '‌', 'آ'], ['ی', 'ک', ' ', 'ا'], $value);
        return mb_strtolower(trim($value), 'UTF-8');
    }

    private function calcScore(string $fieldValue, string $query, int $baseWeight): float
    {
        if ($fieldValue === '') {
            return 0;
        }

        $value = $this->normalizePersian($fieldValue);
        if ($value === $query) {
            return $baseWeight * 1.5;
        }
        if (mb_strpos($value, $query) === 0) {
            return $baseWeight * 1.2;
        }
        if (mb_strpos($value, $query) !== false) {
            return (float) $baseWeight;
        }
        return 0;
    }

    private function sanitizePublicImageUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        return '';
    }

    /**
     * Remove game-type prefixes from city display labels.
     */
    private function normalizeCityData(?array $cityData, string $productType): ?array
    {
        if (!$cityData || empty($cityData['name'])) {
            return $cityData;
        }

        $cityName = trim((string) $cityData['name']);
        $prefixes = array_unique(array_filter(array_merge([$productType], $this->gameTypeCityPrefixes)));

        foreach ($prefixes as $prefix) {
            $prefix = trim((string) $prefix);
            if ($prefix === '') {
                continue;
            }

            $prefixWithSpace = $prefix . ' ';
            if (mb_strpos($cityName, $prefixWithSpace) === 0) {
                $cityName = trim(mb_substr($cityName, mb_strlen($prefixWithSpace)));
                break;
            }

            if (mb_strpos($cityName, $prefix) === 0) {
                $cityName = trim(mb_substr($cityName, mb_strlen($prefix)));
                break;
            }
        }

        $cityData['name'] = $cityName;
        return $cityData;
    }
}
