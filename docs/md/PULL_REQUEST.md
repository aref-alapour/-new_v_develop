# Pull Request: Team AJAX routing to ezq and teamReq

## Summary
- All AJAX requests on team pages (marketing_report, sales_report, transactions, sms, game_finder, and other team pages) were moved from `admin-ajax.php` to the obfuscated endpoint `/ezq` (action 0) using the `teamReq` / `window.ezAjax(0, params, opts)` mechanism.
- Team callbacks were moved from `template/team/ajax/callbacks/` to `app/ajax/callbacks/team/` and are executed via SecureAjax with token and encryption.
- Callbacks that previously returned HTML now respond with JSON via `wp_send_json_success(['html' => ...])`, and the frontend uses `res.data.html`.

## Why
- Remove reliance on the exposed `admin-ajax.php` URL and reduce attack surface.
- Align with the secure AJAX system (SecureAjax): obfuscated URL, encrypted payload, token-based auth.
- Centralized team callbacks under `app/ajax/callbacks/team/` for easier maintenance.

## How
- In the team layout, `window.ezTeamAjax` (url, token, nonce, csrf) and `window.ezAjax` are set to post to `/ezq`.
- Each team page defines a local `teamReq(data, opts)` that merges params with `handler: 'team'` and `nonce`, then calls `ezAjax(0, p, opts)`.
- All `fetch(admin-ajax, FormData)` (and similar) were replaced with `teamReq(params, { success, error, complete })`.
- Callbacks live in `app/ajax/callbacks/team/` under the same filenames (e.g. `marketing_report_search.php`); SecureAjax runs them after merging `$params` into `$_POST`.

## Risk
- Only pages under `/team/` and the user panel are affected; the rest of the site uses existing routes or other endpoints.
- Marketing report export (CSV/Excel) on marketing_report still uses form submit to admin-ajax for file download; it can be moved later to JSON + base64 and teamReq if needed.

## Test
- Manual test in local: open each team URL (marketing_report, sales_report, transactions, sms, game_finder), run search/filter/pagination/form submit, and confirm correct data and no network or 403 errors.
- In DevTools, confirm requests go to `/ezq` (POST) and responses are JSON.
