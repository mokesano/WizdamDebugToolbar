<?php

declare(strict_types=1);

/**
 * This file is part of WizdamDebugToolbar library.
 *
 * (c) Wizdam Frontedge <info@wizdam.org>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * ---
 * ATTRIBUTION NOTICE:
 * This file was adapted from CodeIgniter 4 Debug Toolbar Collector.
 * Original: system/Debug/Toolbar/Collectors/Config.php
 * Copyright (c) 2014-2024 British Columbia Institute of Technology
 * Licensed under MIT License
 * ---
 */

namespace WizdamDebugToolbar\Collectors;

/**
 * Debug toolbar configuration
 *
 * Adapted from CodeIgniter 4 to be framework-agnostic.
 * Returns basic PHP and environment information.
 */
class Config extends BaseCollector
{
    /**
     * Return toolbar config values as an array.
     */
    public static function display(): array
    {
        return [
            'phpVersion'  => PHP_VERSION,
            'phpSAPI'     => PHP_SAPI,
            'timezone'    => date_default_timezone_get(),
            'serverOS'    => PHP_OS,
        ];
    }
}
