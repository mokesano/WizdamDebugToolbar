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
     * Child classes should implement this to return the timeline data
     * formatted for correct usage.
     */
    protected function formatTimelineData(): array
    {
        $data = [];

        $benchmark = service('timer', true);
        $rows      = $benchmark->getTimers(6);

        foreach ($rows as $name => $info) {
            if ($name === 'total_execution') {
                continue;
            }

            $data[] = [
                'name'      => ucwords(str_replace('_', ' ', $name)),
                'component' => 'Timer',
                'start'     => $info['start'],
                'duration'  => $info['end'] - $info['start'],
            ];
        }

        return $data;
    }
}
