# EZ Core tests (Pest)

Canonical test runner for `ez_core` modules and the EZ AJAX gateway integration.

## Requirements

- PHP **8.5+** (matches `composer.json`)
- MySQL reachable with credentials from `wp-config.php` (via `ez-ajax-gateway/secrets-bootstrap.php`)
- For HTTP Feature tests: gateway at `http://wo.escapezoom.local/ajax` (or override env)

## Install

`composer.json` lists Pest in `require-dev`, but **`composer.lock` must be updated first** (do not run plain `composer install` until lock includes Pest).

```bash
cd wp-content/mu-plugins/ez_core
composer update pestphp/pest pestphp/pest-plugin-arch --with-all-dependencies
```

### If Packagist fails (curl 35 / timeout / 402)

This is a **network/mirror** issue, not a project bug. Symptoms:

- WSL: `OpenSSL SSL_connect: Connection reset by peer` → `repo.packagist.org`
- Global mirror `mirror-composer.runflare.com` → HTTP 402

Try, in order:

1. **VPN** on, then:

   ```bash
   composer config -g --unset repos.packagist
   composer update pestphp/pest pestphp/pest-plugin-arch -W
   ```

2. **Another mirror** (project-local, remove when done):

   ```bash
   composer config repos.packagist composer https://mirrors.aliyun.com/composer/
   composer update pestphp/pest pestphp/pest-plugin-arch -W
   composer config --unset repos.packagist
   ```

3. **Windows PowerShell** (sometimes better SSL than WSL) from `ez_core`, same commands.

4. **Another machine/CI** with working Packagist: run `composer update` there, commit `composer.lock` + `vendor/` is optional (prefer lock only).

Until Pest is installed, use procedural smoke:

```bash
php wp-content/mu-plugins/ez-ajax-gateway/tests/run-full-gateway-tests.php
```

Inside Docker (when `composer` is not on PATH):

```bash
bash wp-content/mu-plugins/ez_core/scripts/install-test-deps.sh
```

Or from the host (WSL) against the mounted tree:

```bash
cd wp-content/mu-plugins/ez_core && composer install
```

## Run

```bash
composer test              # all Pest tests
composer test:unit         # Unit only
composer test:feature      # Feature only (HTTP)
composer test:gateway      # Gateway-related Unit + Feature

./vendor/bin/pest --testsuite=Gateway
./vendor/bin/pest --testsuite=Arch
./vendor/bin/pest --group=http
```

**Reference environment (full green):** Docker WordPress container with DB + `wo.escapezoom.local`:

```bash
docker exec <wordpress-container> bash -c "cd /var/www/html/wp-content/mu-plugins/ez_core && composer test:gateway"
docker exec <wordpress-container> php /var/www/html/wp-content/mu-plugins/ez-ajax-gateway/tests/run-full-gateway-tests.php
```

On Windows/WSL host without `pdo_mysql`, DB unit tests are **skipped** (expected). HTTP feature tests may still pass if the gateway URL is reachable.

## Environment

| Variable | Default | Purpose |
|----------|---------|---------|
| `EZ_AJAX_TEST_BASE_URL` | `http://wo.escapezoom.local/ajax` | Feature HTTP tests |
| `EZ_AJAX_SHARED_SECRET` | from `wp-config.php` via secrets bootstrap | HMAC signing |

## Quick smoke (procedural)

```bash
php wp-content/mu-plugins/ez-ajax-gateway/tests/run-full-gateway-tests.php
```

Use for DevOps one-shot checks; Pest is the maintainable suite for CI and new modules.

## Adding tests for a new module

1. Copy `tests/Templates/ModuleReadServiceTest.template.php` into `tests/Unit/{YourModule}/`.
2. Use `TestCase` (`skipUnlessDb()`, `bootTestEnvironment()` via `setUp`).
3. Use `SchemaTableCheck` / `SchemaAssertions` for DDL checks — not `SHOW TABLES LIKE ?`.
