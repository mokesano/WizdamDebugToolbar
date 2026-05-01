<?php
/** @var array $files */
?>
<table>
    <thead>
        <tr>
            <th>Action</th>
            <th>Datetime</th>
            <th>Status</th>
            <th>Method</th>
            <th>URL</th>
            <th>Content-Type</th>
            <th>Is AJAX?</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($files as $file) : ?>
        <tr data-active="<?= $file['active'] ? 'true' : 'false' ?>">
            <td class="debug-bar-width70p">
                <button class="ci-history-load"
                    data-time="<?= htmlspecialchars($file['time'], ENT_QUOTES, 'UTF-8') ?>">Load</button>
            </td>
            <td class="debug-bar-width190p">
                <?= htmlspecialchars($file['datetime'], ENT_QUOTES, 'UTF-8') ?>
            </td>
            <td><?= (int) $file['status'] ?></td>
            <td><?= htmlspecialchars($file['method'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($file['url'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($file['contentType'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($file['isAJAX'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
