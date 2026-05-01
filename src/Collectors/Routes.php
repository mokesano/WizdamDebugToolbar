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
 * Original: system/Debug/Toolbar/Collectors/Routes.php
 * Copyright (c) 2014-2024 British Columbia Institute of Technology
 * Licensed under MIT License
 * ---
 */

namespace WizdamDebugToolbar\Collectors;

use WizdamDebugToolbar\Interfaces\RouterInterface;

/**
 * Routes collector
 *
 * Adapted from CodeIgniter 4 to be framework-agnostic.
 *
 * Usage:
 *   Routes::setRouter(new WizdamRouterAdapter());
 */
class Routes extends BaseCollector
{
    /**
     * Whether this collector has data that can
     * be displayed in the Timeline.
     *
     * @var bool
     */
    protected $hasTimeline = false;

    /**
     * Whether this collector needs to display
     * content in a tab or not.
     *
     * @var bool
     */
    protected $hasTabContent = true;

    /**
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'Routes';

    /**
     * Registered router adapter, shared across all instances.
     */
    private static ?RouterInterface $router = null;

    /**
     * Register the router adapter.
     * Call this once at bootstrap before the toolbar is rendered.
     */
    public static function setRouter(RouterInterface $router): void
    {
        self::$router = $router;
    }

    /**
     * Returns the data of this collector to be formatted in the toolbar.
     *
     * @return array{
     *      matchedRoute: list<array{
     *          directory: string,
     *          controller: string,
     *          method: string,
     *          paramCount: int,
     *          truePCount: int,
     *          params: list<array{name: string, value: mixed}>
     *      }>,
     *      routes: list<array{method: string, route: string, handler: string}>
     * }
     */
    public function display(): array
    {
        if (self::$router === null) {
            return [
                'matchedRoute' => [],
                'routes'       => [],
            ];
        }

        $rawParams = self::$router->getParams();
        $params    = [];

        foreach ($rawParams as $name => $value) {
            $params[] = [
                'name'  => (string) $name,
                'value' => is_scalar($value) ? (string) $value : print_r($value, true),
            ];
        }

        $matchedRoute = [
            [
                'directory'  => '',
                'controller' => self::$router->getController(),
                'method'     => self::$router->getMethod(),
                'paramCount' => count($rawParams),
                'truePCount' => count($params),
                'params'     => $params,
            ],
        ];

        return [
            'matchedRoute' => $matchedRoute,
            'routes'       => [],
        ];
    }

    /**
     * Returns the number of matched route entries as the badge value.
     */
    public function getBadgeValue(): int
    {
        return self::$router !== null ? 1 : 0;
    }

    /**
     * Does this collector have any data collected?
     */
    public function isEmpty(): bool
    {
        return self::$router === null;
    }

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAFDSURBVEhL7ZRNSsNQFIUjVXSiOFEcuQIHDpzpxC0IGYeE/BEInbWlCHEDLsSiuANdhKDjgm6ggtSJ+l25ldrmmTwIgtgDh/t37r1J+16cX0dRFMtpmu5pWAkrvYjjOB7AETzStBFW+inxu3KUJMmhludQpoflS1zXban4LYqiO224h6VLTHr8Z+z8EpIHFF9gG78nDVmW7UgTHKjsCyY98QP+pcq+g8Ku2s8G8X3f3/I8b038WZTp+bO38zxfFd+I6YY6sNUvFlSDk9CRhiAI1jX1I9Cfw7GG1UB8LAuwbU0ZwQnbRDeEN5qqBxZMLtE1ti9LtbREnMIuOXnyIf5rGIb7Wq8HmlZgwYBH7ORTcKH5E4mpjeGt9fBZcHE2GCQ3Vt7oTNPNg+FXLHnSsHkw/FR+Gg2bB8Ptzrst/v6C/wrH+QB+duli6MYJdQAAAABJRU5ErkJggg==';
    }

    /**
     * Reset the registered router.
     */
    public static function reset(): void
    {
        self::$router = null;
    }
}
