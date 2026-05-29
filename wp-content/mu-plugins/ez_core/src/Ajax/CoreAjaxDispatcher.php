<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Ajax;
use EscapeZoom\Core\Modules\Booking\Actions\SortProductsGetAction;
use EscapeZoom\Core\Modules\Search\Actions\SearchGamesAction;
use EscapeZoom\Core\Modules\Analytics\Actions\RecordProductViewAction;
use EscapeZoom\Core\Modules\Booking\Actions\ManageSansStatusAction;

final class CoreAjaxDispatcher {
    public function handle(string $callback, array $data): mixed {
        switch ($callback) {
            case 'sort_products_get': return (new SortProductsGetAction())->execute($data);
            case 'queryable_search': return (new SearchGamesAction())->execute($data['term']??'');
            case 'post_view_process': return (new RecordProductViewAction())->execute((int)($data['pid']??0), $_SERVER['REMOTE_ADDR']??'', $_SERVER['HTTP_USER_AGENT']??'', $_SERVER['HTTP_REFERER']??'');
            case "get_suggested_games": return (new \EscapeZoom\Core\Modules\Search\Actions\GetSuggestedGamesAction())->execute((int)($data["tag_id"]??0), (string)($data["slug"]??""));
            case 'baz_kon': return (new ManageSansStatusAction())->execute((int)($data['pid']??0), (int)($data['ts']??0), true);
            case 'hazf_kon': return (new ManageSansStatusAction())->execute((int)($data['pid']??0), (int)($data['ts']??0), false);
        }
        return null;
    }
}
