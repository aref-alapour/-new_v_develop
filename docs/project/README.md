# EscapeZoom — project documentation (canonical)

This folder is the **only place for new documentation** while the platform is rebuilt.

## Rules

- **Write here:** module specs, API notes, architecture decisions for new work.
- **Do not add files to** `docs/md/` (legacy, read-only).
- **Cursor rule:** `docs/.cursor/rules/04-docs-workflow-and-pest.mdc`

## Layout

```
docs/project/
├── README.md           ← this file
├── modules/
│   ├── auth.md         ← one file per core module (create as you build)
│   └── ...
└── {topic}.md          ← cross-cutting guides (e.g. mobile-api-v1.md later)
```

## Cutover playbooks

Migration runbooks live next to the core code:

`escapezoom-core/docs/rollout/{module}-cutover.md`

Follow **Rule 01** (zero-downtime phases) in those documents.

## Legacy references (read only)

| Path | Use |
|------|-----|
| `docs/md/` | Old analyses and reports |
| `docs/web-service-doc/` | Legacy web-service API |
| `wp-content/themes/escapezoom-v2/` | Live production theme (runtime) |
| `web-service/web-service.php` | Legacy HTTP API surface |

When legacy docs conflict with Cursor rules 01–04, **rules win**.
