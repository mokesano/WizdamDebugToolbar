<?php

declare(strict_types=1);

namespace Wizdam\DebugBar\Collectors;

/**
 * Debug toolbar configuration
 */
class Config
{
    /**
     * Return toolbar config values as an array.
     */
    public static function display(array $config = []): array
    {
        return [
            'ciVersion'   => '',
            'phpVersion'  => PHP_VERSION,
            'phpSAPI'     => PHP_SAPI,
            'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'production',
            'baseURL'     => $config['baseURL'] ?? '',
            'timezone'    => date_default_timezone_get(),
            'locale'      => $config['locale'] ?? 'en',
            'cspEnabled'  => $config['cspEnabled'] ?? false,
        ];
    }
}
