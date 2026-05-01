<?php
/** @var array $events */
?>
<table>
    <thead>
        <tr>
            <th class="debug-bar-width6r">Time</th>
            <th>Event Name</th>
            <th>Times Called</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($events as $row) : ?>
        <tr>
            <td class="narrow"><?= htmlspecialchars($row['duration'], ENT_QUOTES, 'UTF-8') ?> ms</td>
            <td><?= htmlspecialchars($row['event'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int) $row['count'] ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
