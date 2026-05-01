<?php
/**
 * @var array $matchedRoute
 * @var array $routes
 */
?>
<h3>Matched Route</h3>

<table>
    <tbody>
    <?php foreach ($matchedRoute as $route) : ?>
        <tr>
            <td>Directory:</td>
            <td><?= htmlspecialchars($route['directory'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>Controller:</td>
            <td><?= htmlspecialchars($route['controller'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>Method:</td>
            <td><?= htmlspecialchars($route['method'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>Params:</td>
            <td><?= (int) $route['paramCount'] ?> / <?= (int) $route['truePCount'] ?></td>
        </tr>
        <?php foreach ($route['params'] as $param) : ?>
            <tr class="route-params-item">
                <td><?= htmlspecialchars($param['name'],  ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $param['value'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach ?>
    <?php endforeach ?>
    </tbody>
</table>

<h3>Defined Routes</h3>

<table>
    <thead>
        <tr>
            <th>Method</th>
            <th>Route</th>
            <th>Handler</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($routes === []) : ?>
        <tr><td colspan="3" class="muted">No defined routes registered.</td></tr>
    <?php else : ?>
        <?php foreach ($routes as $route) : ?>
            <tr>
                <td><?= htmlspecialchars($route['method'],  ENT_QUOTES, 'UTF-8') ?></td>
                <td data-debugbar-route="<?= htmlspecialchars($route['method'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($route['route'],   ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td><?= htmlspecialchars($route['handler'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach ?>
    <?php endif ?>
    </tbody>
</table>
