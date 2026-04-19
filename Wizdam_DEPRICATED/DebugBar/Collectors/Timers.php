<?php

declare(strict_types=1);

namespace Wizdam\DebugBar\Collectors;

/**
 * Timers collector
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
     * @var array|null
     */
    private $timers = null;

    /**
     * Set timers data manually
     */
    public function setTimers(array $timers): void
    {
        $this->timers = $timers;
    }

    /**
     * Child classes should implement this to return the timeline data
     * formatted for correct usage.
     */
    protected function formatTimelineData(): array
    {
        $data = [];

        if ($this->timers === null) {
            return $data;
        }

        foreach ($this->timers as $name => $info) {
            if ($name === 'total_execution') {
                continue;
            }

            $data[] = [
                'name'      => ucwords(str_replace('_', ' ', $name)),
                'component' => 'Timer',
                'start'     => $info['start'] ?? 0,
                'duration'  => ($info['end'] ?? 0) - ($info['start'] ?? 0),
            ];
        }

        return $data;
    }
}
