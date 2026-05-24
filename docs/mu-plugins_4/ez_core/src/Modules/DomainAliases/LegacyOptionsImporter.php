<?php

namespace EscapeZoom\Core\Modules\DomainAliases;

/**
 * One-time import from Fanous "wordpress-landing-page-*" options.
 * page_on_front / show_on_front are not imported — blog index + home.php remain authoritative.
 */
final class LegacyOptionsImporter
{
    private const FLAG = 'ez_domain_aliases_imported_legacy_landing';

    public static function register(): void
    {
        // Runs before Runtime::onPluginsLoaded() so the first front-end request sees imported config.
        add_action('plugins_loaded', [self::class, 'maybeImport'], 1);
    }

    public static function maybeImport(): void
    {
        if (get_option(self::FLAG, '')) {
            return;
        }

        $legacyMain = get_option('wordpress-landing-page-options');
        $legacyDomains = get_option('wordpress-landing-page-domains');

        if (!is_array($legacyMain) && !is_array($legacyDomains)) {
            return;
        }

        $cfg = Runtime::loadConfig();
        if (($cfg['main_host'] ?? '') !== '' || !empty($cfg['domains'])) {
            update_option(self::FLAG, '1', false);

            return;
        }

        if (is_array($legacyMain)) {
            $cfg['main_host'] = isset($legacyMain['main_domain']) ? Runtime::canonicalHostKey((string) $legacyMain['main_domain']) : '';
            $cfg['fallback'] = isset($legacyMain['default_action']) && (string) $legacyMain['default_action'] === 'none' ? 'strict' : 'accept_all';
            $cfg['force_ssl_global'] = !empty($legacyMain['use_ssl']);
        }

        $domains = [];
        if (is_array($legacyDomains)) {
            foreach ($legacyDomains as $rawHost => $row) {
                $h = Runtime::canonicalHostKey((string) $rawHost);
                if ($h === '') {
                    continue;
                }
                if (!is_array($row)) {
                    $domains[$h] = ['redirect_url' => '', 'force_ssl' => false];

                    continue;
                }
                $mode = isset($row['show_on_front']) ? (string) $row['show_on_front'] : '';
                if ($mode === 'redirect' && !empty($row['redirect_to'])) {
                    $domains[$h] = [
                        'redirect_url' => esc_url_raw((string) $row['redirect_to']),
                        'force_ssl' => !empty($row['use_ssl']),
                    ];
                } else {
                    $domains[$h] = [
                        'redirect_url' => '',
                        'force_ssl' => !empty($row['use_ssl']),
                    ];
                }
            }
        }

        $cfg['domains'] = $domains;

        update_option(Runtime::optionKey(), $cfg, false);
        update_option(self::FLAG, '1', false);
    }
}
