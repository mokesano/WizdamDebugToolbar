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
 * Original: system/Debug/Toolbar/Collectors/Database.php
 * Copyright (c) 2014-2024 British Columbia Institute of Technology
 * Licensed under MIT License
 * ---
 */

namespace WizdamDebugToolbar\Collectors;

use WizdamDebugToolbar\Interfaces\DatabaseAdapterInterface;

/**
 * Collector for the Database tab of the Debug Toolbar.
 *
 * Adapted from CodeIgniter 4 to be framework-agnostic.
 * Use setAdapter() to register a DatabaseAdapterInterface implementation
 * (e.g., AdodbDatabaseAdapter) before the toolbar is rendered.
 *
 * Example:
 *   Database::setAdapter(new AdodbDatabaseAdapter());
 */
class Database extends BaseCollector
{
    /**
     * Whether this collector has timeline data.
     *
     * @var bool
     */
    protected $hasTimeline = true;

    /**
     * Whether this collector should display its own tab.
     *
     * @var bool
     */
    protected $hasTabContent = true;

    /**
     * The name used to reference this collector in the toolbar.
     *
     * @var string
     */
    protected $title = 'Database';

    /**
     * Registered database adapter, shared across all instances.
     */
    private static ?DatabaseAdapterInterface $adapter = null;

    /**
     * Register the database adapter.
     * Call this once at bootstrap before the toolbar is rendered.
     */
    public static function setAdapter(DatabaseAdapterInterface $adapter): void
    {
        self::$adapter = $adapter;
    }

    /**
     * Returns timeline data formatted for the toolbar.
     */
    protected function formatTimelineData(): array
    {
        if (self::$adapter === null) {
            return [];
        }

        $data = [];

        foreach (self::$adapter->getQueries() as $query) {
            $startTime = (float) ($query['startTime'] ?? 0);
            $duration  = ((float) ($query['duration'] ?? 0)) / 1000; // ms → seconds

            $data[] = [
                'name'      => 'Query',
                'component' => 'Database',
                'start'     => $startTime,
                'duration'  => $duration,
                'query'     => htmlspecialchars($query['sql'] ?? '', ENT_QUOTES, 'UTF-8'),
            ];
        }

        return $data;
    }

    /**
     * Returns the data of this collector to be formatted in the toolbar.
     */
    public function display(): array
    {
        if (self::$adapter === null) {
            return ['queries' => []];
        }

        $rawQueries = self::$adapter->getQueries();
        $sqlCounts  = array_count_values(array_column($rawQueries, 'sql'));

        $queries = [];
        $idx     = 0;

        foreach ($rawQueries as $query) {
            $sql         = $query['sql'] ?? '';
            $isDuplicate = ($sqlCounts[$sql] ?? 1) > 1;

            $queries[] = [
                'hover'      => $isDuplicate ? 'This query was called more than once.' : '',
                'class'      => $isDuplicate ? 'duplicate' : '',
                'duration'   => number_format((float) ($query['duration'] ?? 0), 2) . ' ms',
                'sql'        => htmlspecialchars($sql, ENT_QUOTES, 'UTF-8'),
                'trace'      => $query['trace'] ?? '',
                'trace-file' => $query['trace'] ?? '',
                'qid'        => md5($sql . $idx),
            ];

            $idx++;
        }

        return ['queries' => $queries];
    }

    /**
     * Gets the "badge" value for the button.
     */
    public function getBadgeValue(): int
    {
        return self::$adapter !== null ? self::$adapter->getQueryCount() : 0;
    }

    /**
     * Information to be displayed next to the title.
     */
    public function getTitleDetails(): string
    {
        if (self::$adapter === null) {
            return '';
        }

        $total      = self::$adapter->getQueryCount();
        $duplicates = count(self::$adapter->getDuplicates());

        return sprintf(
            '(%d total Quer%s, %d duplicate)',
            $total,
            $total === 1 ? 'y' : 'ies',
            $duplicates,
        );
    }

    /**
     * Does this collector have any data collected?
     */
    public function isEmpty(): bool
    {
        return self::$adapter === null || self::$adapter->getQueryCount() === 0;
    }

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADMSURBVEhLY6A3YExLSwsA4nIycQDIDIhRWEBqamo/UNF/SjDQjF6ocZgAKPkRiFeEhoYyQ4WIBiA9QAuWAPEHqBAmgLqgHcolGQD1V4DMgHIxwbCxYD+QBqcKINseKo6eWrBioPrtQBq/BcgY5ht0cUIYbBg2AJKkRxCNWkDQgtFUNJwtABr+F6igE8olGQD114HMgHIxAVDyAhA/AlpSA8RYUwoeXAPVex5qHCbIyMgwBCkAuQJIY00huDBUz/mUlBQDqHGjgBjAwAAACexpph6oHSQAAAAASUVORK5CYII=';
    }

    /**
     * Reset collector state.
     */
    public static function reset(): void
    {
        self::$adapter = null;
    }
}
