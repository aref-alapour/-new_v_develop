# EZ-Query API (REST)

## Overview

Frontend **must** use the REST endpoint for game/product data instead of `admin-ajax.php?action=ez_query` (rules 13, 14: no admin-ajax for frontend data). Same response format: `{ success, data, errors }`.

## Endpoint

- **URL:** `GET` or `POST` `/wp-json/escapezoom/v1/query`
- **Authentication:** None required for `get_game` and `list_games`.

## Parameters (GET query or POST JSON body)

| Parameter     | Type   | Required | Description |
|--------------|--------|----------|-------------|
| `action`     | string | yes      | `get_game` or `list_games` |
| `id`         | int    | for get_game | Product/game ID |
| `fields`     | array  | no       | Fields to return (list_games) |
| `with`       | array  | no       | Relations to load |
| `per_page`   | int    | no       | Default 20 (list_games) |
| `city_id`    | int    | no       | Filter by city (list_games) |
| `game_type_id` | int  | no       | Filter by game type (list_games) |

## Examples

**GET (list_games):**
```
GET /wp-json/escapezoom/v1/query?action=list_games&per_page=10&city_id=1
```

**GET (get_game):**
```
GET /wp-json/escapezoom/v1/query?action=get_game&id=123
```

**POST (JSON body):**
```json
{
  "action": "list_games",
  "per_page": 20,
  "city_id": 1,
  "game_type_id": 2,
  "with": ["slots"]
}
```

## Response

- **200** with body: `{ "success": true|false, "data": ... | null, "errors": [] }`
- Same structure as the legacy admin-ajax handler.

## Legacy admin-ajax

The legacy handlers `wp_ajax_ez_query` and `wp_ajax_nopriv_ez_query` have been removed from the core plugin. All consumers **must** use the REST endpoint described above.
