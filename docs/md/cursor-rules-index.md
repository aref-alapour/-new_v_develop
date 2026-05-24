# Cursor rules index (EscapeZoom)

Rules live in `docs/.cursor/rules/`. Files with `alwaysApply: true` apply to every agent session in this workspace.

| File | Summary |
|------|---------|
| [01-zero-downtime-migration.mdc](../.cursor/rules/01-zero-downtime-migration.mdc) | No big-bang; 3-phase migration; dual-write, merged read, backfill, feature flags |
| [02-core-architecture-and-stack.mdc](../.cursor/rules/02-core-architecture-and-stack.mdc) | Modular core, `database-schema/`, MVC, AJAX gateway, Composer stack, gradual v3 UI |

## Priority

1. **Rule 01** wins when a shortcut conflicts with phased migration or risks downtime.
2. **Rule 02** defines where code lives (core vs theme), schema files, MVC, and technology stack.

## Related docs

- Order cutover example: `docs/mu-plugins/escapezoom-core/docs/rollout/order-cutover-playbook.md`
- Reference theme stack: `docs/escapezoom-v3/package.json`
- Reference core packages: `docs/mu-plugins_4/ez_core/composer.json`
