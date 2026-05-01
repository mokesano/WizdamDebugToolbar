<?php
/**
 * @var string $phpVersion
 * @var string $phpSAPI
 * @var string $timezone
 * @var string $serverOS
 * @var string $baseURL
 * @var string $environment
 */
?>
<table>
    <tbody>
        <tr>
            <td>WizdamDebugToolbar Version:</td>
            <td><?= htmlspecialchars(\WizdamDebugToolbar\DebugToolbar::VERSION, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>PHP Version:</td>
            <td><?= htmlspecialchars($phpVersion, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>PHP SAPI:</td>
            <td><?= htmlspecialchars($phpSAPI, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>Server OS:</td>
            <td><?= htmlspecialchars($serverOS, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>Environment:</td>
            <td><?= htmlspecialchars($environment, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>Base URL:</td>
            <td>
                <?php if ($baseURL === '') : ?>
                    <span class="warning">baseURL not configured.</span>
                <?php else : ?>
                    <?= htmlspecialchars($baseURL, ENT_QUOTES, 'UTF-8') ?>
                <?php endif ?>
            </td>
        </tr>
        <tr>
            <td>Timezone:</td>
            <td><?= htmlspecialchars($timezone, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    </tbody>
</table>
