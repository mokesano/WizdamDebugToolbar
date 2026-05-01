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
 * Original: system/Debug/Toolbar/Collectors/Events.php
 * Copyright (c) 2014-2024 British Columbia Institute of Technology
 * Licensed under MIT License
 * ---
 */

namespace WizdamDebugToolbar\Collectors;

/**
 * Events collector
 *
 * Adapted from CodeIgniter 4 to be framework-agnostic.
 *
 * Usage:
 *   $start = microtime(true);
 *   // ... event handler runs ...
 *   Events::trigger('my_event', $start, microtime(true));
 */
class Events extends BaseCollector
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
    protected $hasTabContent = true;

    /**
     * Whether this collector has data that
     * should be shown in the Vars tab.
     *
     * @var bool
     */
    protected $hasVarData = false;

    /**
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'Events';

    /**
     * @var list<array{event: string, start: float, end: float}>
     */
    private static array $logs = [];

    /**
     * Log a triggered event.
     *
     * @param float $start microtime(true) before the event handler ran
     * @param float $end   microtime(true) after the event handler returned
     */
    public static function trigger(string $event, float $start, float $end): void
    {
        self::$logs[] = [
            'event' => $event,
            'start' => $start,
            'end'   => $end,
        ];
    }

    /**
     * Child classes should implement this to return the timeline data
     * formatted for correct usage.
     */
    protected function formatTimelineData(): array
    {
        $data = [];

        foreach (self::$logs as $info) {
            $data[] = [
                'name'      => 'Event: ' . $info['event'],
                'component' => 'Events',
                'start'     => $info['start'],
                'duration'  => $info['end'] - $info['start'],
            ];
        }

        return $data;
    }

    /**
     * Returns the data of this collector to be formatted in the toolbar
     */
    public function display(): array
    {
        $events = [];

        foreach (self::$logs as $row) {
            $key = $row['event'];

            if (! array_key_exists($key, $events)) {
                $events[$key] = [
                    'event'    => $key,
                    'duration' => ($row['end'] - $row['start']) * 1000,
                    'count'    => 1,
                ];

                continue;
            }

            $events[$key]['duration'] += ($row['end'] - $row['start']) * 1000;
            $events[$key]['count']++;
        }

        foreach ($events as &$row) {
            $row['duration'] = number_format($row['duration'], 2);
        }

        return ['events' => array_values($events)];
    }

    /**
     * Gets the "badge" value for the button.
     */
    public function getBadgeValue(): int
    {
        return count(self::$logs);
    }

    /**
     * Does this collector have any data collected?
     */
    public function isEmpty(): bool
    {
        return self::$logs === [];
    }

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAEASURBVEhL7ZXNDcIwDIVTsRBH1uDQDdquUA6IM1xgCA6MwJUN2hk6AQzAz0vl0ETUxC5VT3zSU5w81/mRMGZysixbFEVR0jSKNt8geQU9aRpFmp/keX6AbjZ5oB74vsaN5lSzA4tLSjpBFxsjeSuRy4d2mDdQTWU7YLbXTNN05mKyovj5KL6B7q3hoy3KwdZxBlT+Ipz+jPHrBqOIynZgcZonoukb/0ckiTHqNvDXtXEAaygRbaB9FvUTjRUHsIYS0QaSp+Dw6wT4hiTmYHOcYZsdLQ2CbXa4ftuuYR4x9vYZgdb4vsFYUdmABMYeukK9/SUme3KMFQ77+Yfzh8eYF8+orDuDWU5LAAAAAElFTkSuQmCC';
    }

    /**
     * Reset all logged events.
     */
    public static function reset(): void
    {
        self::$logs = [];
    }
}
