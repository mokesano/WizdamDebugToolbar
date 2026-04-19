<?php

declare(strict_types=1);

namespace Wizdam\DebugBar\Collectors;

/**
 * Logs collector
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
     * Our collected data.
     *
     * @var list<array{level: string, msg: string}>
     */
    protected $data = [];

    /**
     * Returns the data of this collector to be formatted in the toolbar.
     *
     * @return array{logs: list<array{level: string, msg: string}>}
     */
    public function display(): array
    {
        return [
            'logs' => $this->data,
        ];
    }

    /**
     * Does this collector actually have any data to display?
     */
    public function isEmpty(): bool
    {
        return $this->data === [];
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
     * Set logs data manually
     *
     * @param list<array{level: string, msg: string}> $logs
     */
    public function setLogs(array $logs): void
    {
        $this->data = $logs;
    }
}
