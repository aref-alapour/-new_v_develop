# Cursor rules index (EscapeZoom)

Rules live in `docs/.cursor/rules/`. Files with `alwaysApply: true` apply to every agent session in this workspace.

| File | Summary |
|------|---------|
| [01-zero-downtime-migration.mdc](../.cursor/rules/01-zero-downtime-migration.mdc) | No big-bang; 3-phase migration; dual-write, merged read, backfill, feature flags |
| [02-core-architecture-and-stack.mdc](../.cursor/rules/02-core-architecture-and-stack.mdc) | Modular core, `database-schema/`, MVC, AJAX gateway, Composer stack, gradual v3 UI |
| [03-security-performance-red-lines.mdc](../.cursor/rules/03-security-performance-red-lines.mdc) | Security → performance → UX; data/query red lines; no new admin-ajax; front libs; Jobs; audit |
| [04-docs-workflow-and-pest.mdc](../.cursor/rules/04-docs-workflow-and-pest.mdc) | New docs in `docs/project/`; read-before-code; Pest pyramid; PR DoD; `composer test` for core |
| [05-business-domain-principles.mdc](../.cursor/rules/05-business-domain-principles.mdc) | High-level business invariants; detailed flows in `docs/project/modules/*.md` |

## Priority

1. **Rule 01** wins when a shortcut conflicts with phased migration or risks downtime.
2. **Rule 02** defines where code lives (core vs theme), schema files, MVC, and technology stack.
3. **Rule 03** wins on security, performance, forbidden patterns, and audit — does not override 01 or 02 on migration or structure.
4. **Rule 04** defines documentation paths, workflow, Pest tests, and PR Definition of Done — does not override 01–03.
5. **Rule 05** defines business invariants — detailed formulas and flows live in **`docs/project/modules/`**, not in the rule file.

## New documentation

Write module and feature docs under **[docs/project/](../project/README.md)** only. Do not add files to `docs/md/`.

**Started modules:** [booking.md](../project/modules/booking.md), [checkout.md](../project/modules/checkout.md). Next suggested: `finance.md`, `wallet.md`, `cancel.md`.

## Related docs

- Order cutover example: `docs/mu-plugins/escapezoom-core/docs/rollout/order-cutover-playbook.md`
- Reference theme stack: `docs/escapezoom-v3/package.json`
- Reference core packages: `docs/mu-plugins_4/ez_core/composer.json`
