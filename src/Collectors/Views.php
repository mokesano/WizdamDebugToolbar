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
 * Original: system/Debug/Toolbar/Collectors/Views.php
 * Copyright (c) 2014-2024 British Columbia Institute of Technology
 * Licensed under MIT License
 * ---
 */

namespace WizdamDebugToolbar\Collectors;

/**
 * Views collector
 *
 * Adapted from CodeIgniter 4 to be framework-agnostic.
 *
 * Usage:
 *   $start = microtime(true);
 *   // ... render template ...
 *   Views::logView('article/view.tpl', $start, microtime(true), $templateData);
 */
class Views extends BaseCollector
{
    /**
     * Whether this collector has data that can
     * be displayed in the Timeline.
     *
     * @var bool
     */
    protected $hasTimeline = true;

    /**
     * Whether this collector needs to display
     * content in a tab or not.
     *
     * @var bool
     */
    protected $hasTabContent = false;

    /**
     * Whether this collector needs to display
     * a label or not.
     *
     * @var bool
     */
    protected $hasLabel = true;

    /**
     * Whether this collector has data that
     * should be shown in the Vars tab.
     *
     * @var bool
     */
    protected $hasVarData = true;

    /**
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'Views';

    /**
     * @var list<array{view: string, start: float, end: float, data: array}>
     */
    private static array $renderedViews = [];

    /**
     * Log a rendered view.
     *
     * @param string $view  Template name / path
     * @param float  $start microtime(true) before rendering
     * @param float  $end   microtime(true) after rendering
     * @param array  $data  Variables passed to the template (optional)
     */
    public static function logView(string $view, float $start, float $end, array $data = []): void
    {
        self::$renderedViews[] = [
            'view'  => $view,
            'start' => $start,
            'end'   => $end,
            'data'  => $data,
        ];
    }

    /**
     * Child classes should implement this to return the timeline data
     * formatted for correct usage.
     */
    protected function formatTimelineData(): array
    {
        $data = [];

        foreach (self::$renderedViews as $info) {
            $data[] = [
                'name'      => 'View: ' . $info['view'],
                'component' => 'Views',
                'start'     => $info['start'],
                'duration'  => $info['end'] - $info['start'],
            ];
        }

        return $data;
    }

    /**
     * Gets a collection of data that should be shown in the 'Vars' tab.
     */
    public function getVarData(): array
    {
        $merged = [];

        foreach (self::$renderedViews as $info) {
            foreach ($info['data'] as $key => $value) {
                $merged[(string) $key] = $value;
            }
        }

        return ['View Data' => $merged];
    }

    /**
     * Returns a count of all views rendered.
     */
    public function getBadgeValue(): int
    {
        return count(self::$renderedViews);
    }

    /**
     * Does this collector have any data collected?
     */
    public function isEmpty(): bool
    {
        return self::$renderedViews === [];
    }

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADeSURBVEhL7ZSxDcIwEEWNYA0YgGmgyAaJLTcUaaBzQQEVjMEabBQxAdw53zTHiThEovGTfnE/9rsoRUxhKLOmaa6Uh7X2+UvguLCzVxN1XW9x4EYHzik033Hp3X0LO+DaQG8MDQcuq6qao4qkHuMgQggLvkPLjqh00ZgFDBacMJYFkuwFlH1mshdkZ5JPJURA9JpI6xNCBESvibQ+IURA9JpI6xNCBESvibQ+IURA9DTsuHTOrVFFxixgB/eUFlU8uKJ0eDBFOu/9EvoeKnlJS2/08Tc8NOwQ8sIfMeYFjqKDjdU2sp4AAAAASUVORK5CYII=';
    }

    /**
     * Reset all logged views.
     */
    public static function reset(): void
    {
        self::$renderedViews = [];
    }
}
