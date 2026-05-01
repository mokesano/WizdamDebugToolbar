<?php
/** @var array $queries */
?>
<table>
    <thead>
        <tr>
            <th class="debug-bar-width6r">Time</th>
            <th>Query String</th>
            <th>Caller</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($queries as $query) : ?>
        <tr class="<?= htmlspecialchars($query['class'], ENT_QUOTES, 'UTF-8') ?>"
            title="<?= htmlspecialchars($query['hover'], ENT_QUOTES, 'UTF-8') ?>"
            data-toggle="<?= htmlspecialchars($query['qid'], ENT_QUOTES, 'UTF-8') ?>-trace">
            <td class="narrow"><?= htmlspecialchars($query['duration'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= $query['sql'] ?></td>
            <td class="debug-bar-alignRight">
                <strong><?= htmlspecialchars($query['trace-file'], ENT_QUOTES, 'UTF-8') ?></strong>
            </td>
        </tr>
        <tr class="muted debug-bar-ndisplay"
            id="<?= htmlspecialchars($query['qid'], ENT_QUOTES, 'UTF-8') ?>-trace">
            <td></td>
            <td colspan="2">
                <?= nl2br(htmlspecialchars($query['trace'], ENT_QUOTES, 'UTF-8')) ?>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
