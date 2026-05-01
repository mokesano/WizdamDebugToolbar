<?php
/** @var array $logs */
?>
<?php if ($logs === []) : ?>
<p>Nothing was logged. If you were expecting logged items, call
   <code>WizdamDebugToolbar\Collectors\Logs::addLog($level, $message)</code>
   from your application code.</p>
<?php else : ?>
<table>
    <thead>
        <tr>
            <th>Severity</th>
            <th>Message</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($logs as $entry) : ?>
        <tr>
            <td><?= htmlspecialchars($entry['level'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($entry['msg'],   ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>
