<?php

declare(strict_types=1);

namespace WizdamDebugToolbar\Adapters;

use WizdamDebugToolbar\Interfaces\RouterInterface;

/**
 * WizdamRouterAdapter
 *
 * Implementasi RouterInterface untuk OJS 2.4.8.5.
 *
 * OJS menggunakan routing berbasis parameter request:
 *   - $_REQUEST['page'] → nama handler/controller (mis: 'article', 'index')
 *   - $_REQUEST['op']   → nama method/action     (mis: 'view', 'submit')
 *
 * Routing OJS mengikuti pola:
 *   /index.php?journal=NAME&page=article&op=view&path[]=123
 *
 * Atau dengan URL rewriting:
 *   /journal-name/article/view/123
 *
 * Class OJS yang relevan: PKPRouter, PageRouter, PKPPageRouter
 * (di pkp-lib/classes/core/)
 */
class WizdamRouterAdapter implements RouterInterface
{
    /**
     * Parameter yang merupakan bagian dari routing OJS,
     * bukan parameter bisnis — akan di-exclude dari getParams().
     */
    private const ROUTING_PARAMS = ['page', 'op', 'journal', 'press', 'conference'];

    public function getCurrentRoute(): string
    {
        $page = $this->getController();
        $op   = $this->getMethod();

        return '/' . $page . '/' . $op;
    }

    public function getController(): string
    {
        return $this->sanitize($_REQUEST['page'] ?? 'index');
    }

    public function getMethod(): string
    {
        return $this->sanitize($_REQUEST['op'] ?? 'index');
    }

    public function getParams(): array
    {
        $params = [];

        // Gabungkan GET dan POST, hilangkan parameter routing internal
        $all = array_merge($_GET, $_POST);

        foreach ($all as $key => $value) {
            if (in_array($key, self::ROUTING_PARAMS, true)) {
                continue;
            }
            $params[$this->sanitize((string) $key)] = $this->sanitizeValue($value);
        }

        return $params;
    }

    public function getHttpMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    // ---------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------

    /**
     * OJS path params: ?path[]=volume&path[]=issue
     * Dikembalikan sebagai string: 'volume/issue'
     */
    public function getPathArray(): string
    {
        if (!isset($_REQUEST['path'])) {
            return '';
        }

        $path = $_REQUEST['path'];

        if (is_array($path)) {
            return implode('/', array_map([$this, 'sanitize'], $path));
        }

        return $this->sanitize((string) $path);
    }

    /**
     * Nama jurnal/press yang sedang aktif (konteks OJS multi-journal).
     */
    public function getJournalContext(): string
    {
        return $this->sanitize(
            $_REQUEST['journal'] ?? $_REQUEST['press'] ?? $_SERVER['HTTP_HOST'] ?? 'unknown'
        );
    }

    /**
     * URL lengkap request saat ini.
     */
    public function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    private function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->sanitize($value);
        }

        if (is_array($value)) {
            return array_map(fn($v) => is_string($v) ? $this->sanitize($v) : $v, $value);
        }

        return $value;
    }
}