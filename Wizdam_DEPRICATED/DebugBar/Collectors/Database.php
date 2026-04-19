<?php

declare(strict_types=1);

namespace Wizdam\DebugBar\Collectors;

/**
 * Collector for the Database tab of the Debug Toolbar.
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
     * Whether this collector has data for the Vars tab.
     *
     * @var bool
     */
    protected $hasVarData = false;

    /**
     * The name used to reference this collector in the toolbar.
     *
     * @var string
     */
    protected $title = 'Database';

    /**
     * Array of database connections.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * The query instances that have been collected.
     *
     * @var array
     */
    protected static $queries = [];

    /**
     * Set queries manually
     *
     * @param array $queries Array of query data with keys: query, string, duplicate, trace
     */
    public function setQueries(array $queries): void
    {
        static::$queries = $queries;
    }

    /**
     * Set connections manually
     */
    public function setConnections(array $connections): void
    {
        $this->connections = $connections;
    }

    /**
     * The static method used to collect query data.
     *
     * @param array $query Query data array with keys: query, string, start_time, duration, trace
     */
    public static function collectQuery(array $query): void
    {
        $configMax = 100; // Default max queries

        if (count(static::$queries) < $configMax) {
            $queryString = $query['string'] ?? '';

            $backtrace = $query['trace'] ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            if (! empty($backtrace)) {
                // Simplify backtrace
                foreach ($backtrace as &$line) {
                    if (isset($line['file'])) {
                        $line['file'] = clean_path($line['file']) . ':' . ($line['line'] ?? '');
                        unset($line['line']);
                    }
                }
            }

            static::$queries[] = [
                'query'     => $query,
                'string'    => $queryString,
                'duplicate' => in_array($queryString, array_column(static::$queries, 'string'), true),
                'trace'     => $backtrace,
            ];
        }
    }

    /**
     * Returns timeline data formatted for the toolbar.
     *
     * @return array The formatted data or an empty array.
     */
    protected function formatTimelineData(): array
    {
        $data = [];

        foreach ($this->connections as $alias => $connection) {
            // Connection Time
            $data[] = [
                'name'      => 'Connecting to Database: "' . $alias . '"',
                'component' => 'Database',
                'start'     => $connection->getConnectStart(),
                'duration'  => $connection->getConnectDuration(),
            ];
        }

        foreach (static::$queries as $query) {
            $data[] = [
                'name'      => 'Query',
                'component' => 'Database',
                'start'     => $query['query']->getStartTime(true),
                'duration'  => $query['query']->getDuration(),
                'query'     => $query['query']->debugToolbarDisplay(),
            ];
        }

        return $data;
    }

    /**
     * Returns the data of this collector to be formatted in the toolbar
     */
    public function display(): array
    {
        return ['queries' => array_map(static function (array $query): array {
            $isDuplicate = $query['duplicate'] === true;

            $firstNonSystemLine = '';

            foreach ($query['trace'] as $index => &$line) {
                // simplify file and line
                if (isset($line['file'])) {
                    $line['file'] = clean_path($line['file']) . ':' . $line['line'];
                    unset($line['line']);
                } else {
                    $line['file'] = '[internal function]';
                }

                // find the first trace line that does not originate from `system/`
                if ($firstNonSystemLine === '' && ! str_contains($line['file'], 'SYSTEMPATH')) {
                    $firstNonSystemLine = $line['file'];
                }

                // simplify function call
                if (isset($line['class'])) {
                    $line['function'] = $line['class'] . $line['type'] . $line['function'];
                    unset($line['class'], $line['type']);
                }

                if (strrpos($line['function'], '{closure}') === false) {
                    $line['function'] .= '()';
                }

                $line['function'] = str_repeat(chr(0xC2) . chr(0xA0), 8) . $line['function'];

                // add index numbering padded with nonbreaking space
                $indexPadded = str_pad(sprintf('%d', $index + 1), 3, ' ', STR_PAD_LEFT);
                $indexPadded = preg_replace('/\s/', chr(0xC2) . chr(0xA0), $indexPadded);

                $line['index'] = $indexPadded . str_repeat(chr(0xC2) . chr(0xA0), 4);
            }

            return [
                'hover'      => $isDuplicate ? 'This query was called more than once.' : '',
                'class'      => $isDuplicate ? 'duplicate' : '',
                'duration'   => ((float) $query['query']->getDuration(5) * 1000) . ' ms',
                'sql'        => $query['query']->debugToolbarDisplay(),
                'trace'      => $query['trace'],
                'trace-file' => $firstNonSystemLine,
                'qid'        => md5($query['query'] . Time::now()->format('0.u00 U')),
            ];
        }, static::$queries)];
    }

    /**
     * Gets the "badge" value for the button.
     */
    public function getBadgeValue(): int
    {
        return count(static::$queries);
    }

    /**
     * Information to be displayed next to the title.
     *
     * @return string The number of queries (in parentheses) or an empty string.
     */
    public function getTitleDetails(): string
    {
        $this->getConnections();

        $queryCount      = count(static::$queries);
        $uniqueCount     = count(array_filter(static::$queries, static fn ($query): bool => $query['duplicate'] === false));
        $connectionCount = count($this->connections);

        return sprintf(
            '(%d total Quer%s, %d %s unique across %d Connection%s)',
            $queryCount,
            $queryCount > 1 ? 'ies' : 'y',
            $uniqueCount,
            $uniqueCount > 1 ? 'of them' : '',
            $connectionCount,
            $connectionCount > 1 ? 's' : '',
        );
    }

    /**
     * Does this collector have any data collected?
     */
    public function isEmpty(): bool
    {
        return static::$queries === [];
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
     * Gets the connections from the database config
     */
    private function getConnections(): void
    {
        $this->connections = \Config\Database::getConnections();
    }

    /**
     * Reset collector state for worker mode.
     * Clears collected queries between requests.
     */
    public function reset(): void
    {
        static::$queries = [];
    }
}
