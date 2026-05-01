<?php
/**
 * @var array $userFiles
 * @var array $coreFiles
 */
?>
<table>
    <tbody>
    <?php foreach ($userFiles as $file) : ?>
        <tr>
            <td><?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php endforeach ?>
    <?php foreach ($coreFiles as $file) : ?>
        <tr class="muted">
            <td class="debug-bar-width20e"><?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
