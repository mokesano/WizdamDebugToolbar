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
 * Original: system/Debug/Toolbar/Collectors/Logs.php
 * Copyright (c) 2014-2024 British Columbia Institute of Technology
 * Licensed under MIT License
 * ---
 */

namespace WizdamDebugToolbar\Collectors;

/**
 * Logs collector
 *
 * Adapted from CodeIgniter 4 to be framework-agnostic.
 *
 * Usage:
 *   Logs::addLog('error', 'Something went wrong');
 *   Logs::addLog('info',  'User logged in');
 */
class Logs extends BaseCollector
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
    protected $title = 'Logs';

    /**
     * @var list<array{level: string, msg: string}>
     */
    private static array $logCache = [];

    /**
     * Add a log entry.
     *
     * @param string $level PSR-3 log level: emergency, alert, critical, error, warning, notice, info, debug
     * @param string $msg   Log message
     */
    public static function addLog(string $level, string $msg): void
    {
        self::$logCache[] = [
            'level' => $level,
            'msg'   => $msg,
        ];
    }

    /**
     * Returns the data of this collector to be formatted in the toolbar.
     *
     * @return array{logs: list<array{level: string, msg: string}>}
     */
    public function display(): array
    {
        return ['logs' => self::$logCache];
    }

    /**
     * Does this collector actually have any data to display?
     */
    public function isEmpty(): bool
    {
        return self::$logCache === [];
    }

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACYSURBVEhLYxgFJIHU1FSjtLS0i0D8AYj7gEKMEBkqAaAFF4D4ERCvAFrwH4gDoFIMKSkpFkB+OTEYqgUTACXfA/GqjIwMQyD9H2hRHlQKJFcBEiMGQ7VgAqCBvUgK32dmZspCpagGGNPT0/1BLqeF4bQHQJePpiIwhmrBBEADR1MRfgB0+WgqAmOoFkwANHA0FY0CUgEDAwCQ0PUpNB3kqwAAAABJRU5ErkJggg==';
    }

    /**
     * Reset all log entries.
     */
    public static function reset(): void
    {
        self::$logCache = [];
    }
}
