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
 * Original: system/Debug/Toolbar/Collectors/Timers.php
 * Copyright (c) 2014-2024 British Columbia Institute of Technology
 * Licensed under MIT License
 * ---
 */

namespace WizdamDebugToolbar\Collectors;

/**
 * Timers collector
 *
 * Adapted from CodeIgniter 4 to be framework-agnostic.
 *
 * Usage:
 *   Timers::start('my_block');
 *   // ... code ...
 *   Timers::stop('my_block');
 */
class Timers extends BaseCollector
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
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'Timers';

    /**
     * @var array<string, array{start: float, end: float|null}>
     */
    private static array $timers = [];

    /**
     * Start a named timer.
     */
    public static function start(string $name): void
    {
        self::$timers[$name] = ['start' => microtime(true), 'end' => null];
    }

    /**
     * Stop a named timer.
     */
    public static function stop(string $name): void
    {
        if (isset(self::$timers[$name])) {
            self::$timers[$name]['end'] = microtime(true);
        }
    }

    /**
     * Returns all recorded timers (read-only).
     *
     * @return array<string, array{start: float, end: float|null}>
     */
    public static function getTimers(): array
    {
        return self::$timers;
    }

    /**
     * Child classes should implement this to return the timeline data
     * formatted for correct usage.
     */
    protected function formatTimelineData(): array
    {
        $data = [];

        foreach (self::$timers as $name => $timer) {
            if ($name === 'total_execution') {
                continue;
            }

            $end = $timer['end'] ?? microtime(true);

            $data[] = [
                'name'      => ucwords(str_replace('_', ' ', $name)),
                'component' => 'Timer',
                'start'     => $timer['start'],
                'duration'  => $end - $timer['start'],
            ];
        }

        return $data;
    }

    /**
     * Reset all timers (e.g. between requests in worker mode).
     */
    public static function reset(): void
    {
        self::$timers = [];
    }
}
