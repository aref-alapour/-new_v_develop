# Team/Panel HAR Baseline (Before Native Refactor)

Date: 2026-05-28

This baseline is extracted from:

- `C:\Users\jobal\Desktop\team-sans_management.har`
- `C:\Users\jobal\Desktop\panel-sans-manager.har`

## Action Matrix

| Surface | Action | Endpoint | Wait (ms) observed | X-EZ-Encrypted | X-EZ-Booking-Elapsed-Ms |
|---|---|---|---:|---|---|
| team/sans_management | `booking.sans_management_web` | `/ajax` | ~18437 / ~17791 | not present in capture | not present in capture |
| team/sans_management | `booking.check_playing` | `/ajax` | ~10818 / ~7769 | not present in capture | not present in capture |
| team/sans_management | `ez_team_sans_game_search` | `wp-admin/admin-ajax.php` | ~8103 / ~8582 | n/a | n/a |
| panel/sans-manager | `booking.sans_management_web` | `/ajax` | ~31266 / ~7885 | not present in capture | not present in capture |
| panel/sans-manager | `booking.close_sans` | `/ajax` | ~15091 / ~9830 | present | not present in capture |

## Critical Findings

- Team page response contains duplicate boot script (`ez-ajax-boot`) in this capture.
- Team capture includes malformed image URL with doubled uploads prefix:
  - `.../wp-content/uploads/v.escapezoom.local/wp-content/uploads/...`
- Wait latency is still far above target on team/panel paths in this baseline.
- `/ajax` was previously served through rewrite + `template_redirect` (WordPress lifecycle overhead).
- Existing baseline capture does not contain new phase headers required for native split analysis.

## Required Re-Capture Fields (Post Change)

For each target action, capture these headers:

- `X-EZ-Booking-Elapsed-Ms`
- `X-EZ-Gateway-Phase-Rate-Ms`
- `X-EZ-Gateway-Phase-Auth-Ms`
- `X-EZ-Gateway-Phase-Crypto-Ms`
- `X-EZ-Gateway-Phase-Policy-Ms`
- `X-EZ-Gateway-Phase-Owner-Ms`
- `X-EZ-Gateway-PreDispatch-Ms`
- `X-EZ-Response-Encrypted`

## Notes

- This file is intentionally a before-state matrix.
- A new HAR capture is required after remediation deployment to evaluate pass/fail against final acceptance gates.
