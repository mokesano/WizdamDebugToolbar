<?php

declare(strict_types=1);

namespace WizdamDebugToolbar;

use WizdamDebugToolbar\Collectors\Config;
use WizdamDebugToolbar\Collectors\History;

/**
 * WizdamDebugToolbar DebugToolbar
 *
 * Diadaptasi dari CodeIgniter4 v4.7.2 system/Debug/Toolbar.php
 * Semua dependency CI4 (Services::, service(), WRITEPATH, site_url(), dll)
 * diganti dengan PHP native atau interface agnostik.
 */
class DebugToolbar
{
    public const VERSION = '1.0.0';

    protected array $config;

    /** @var list<\WizdamDebugToolbar\Collectors\BaseCollector> */
    protected array $collectors = [];

    public function __construct(array $config = [])
    {
        $defaults     = require __DIR__ . '/../config/wizdamtoolbar.php';
        $this->config = array_merge($defaults, $config);

        foreach ($this->config['collectors'] as $collectorClass) {
            if (! class_exists($collectorClass)) {
                error_log('WizdamDebugToolbar: Collector tidak ditemukan (' . $collectorClass . ').');
                continue;
            }
            $this->collectors[] = new $collectorClass();
        }
    }

    // ---------------------------------------------------------------
    // Data collection
    // ---------------------------------------------------------------

    /**
     * Kumpulkan semua data dan kembalikan sebagai JSON.
     * Dipanggil di akhir siklus request, sebelum response dikirim.
     *
     * Menggantikan: Toolbar::run(float, float, RequestInterface, ResponseInterface)
     * Perubahan: request/response CI4 diganti dengan superglobal PHP.
     */
    public function run(float $startTime, float $totalTime): string
    {
        $data = [];

        $data['url']              = $this->currentUrl();
        $data['method']           = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $data['isAJAX']           = $this->isAjax();
        $data['startTime']        = $startTime;
        $data['totalTime']        = $totalTime * 1000;
        $data['totalMemory']      = number_format(memory_get_peak_usage() / 1024 / 1024, 3);
        $data['segmentDuration']  = $this->roundTo($data['totalTime'] / 7);
        $data['segmentCount']     = (int) ceil($data['totalTime'] / max($data['segmentDuration'], 0.001));
        $data['DEBUGBAR_VERSION'] = self::VERSION;
        $data['collectors']       = [];

        foreach ($this->collectors as $collector) {
            $data['collectors'][] = $collector->getAsArray();
        }

        // --- Var Data dari collector ---
        foreach ($this->collectVarData() as $heading => $items) {
            $varData = [];
            if (is_array($items)) {
                foreach ($items as $key => $value) {
                    $varData[$this->esc((string) $key)] = is_string($value)
                        ? $this->esc($value)
                        : '<pre>' . $this->esc(print_r($value, true)) . '</pre>';
                }
            }
            $data['vars']['varData'][$this->esc((string) $heading)] = $varData;
        }

        // --- Session ---
        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                if (is_string($value) && preg_match('~[^\x20-\x7E\t\r\n]~', $value)) {
                    $value = 'binary data';
                }
                $data['vars']['session'][$this->esc((string) $key)] = is_string($value)
                    ? $this->esc($value)
                    : '<pre>' . $this->esc(print_r($value, true)) . '</pre>';
            }
        }

        // --- GET ---
        foreach ($_GET as $name => $value) {
            $data['vars']['get'][$this->esc((string) $name)] = is_array($value)
                ? '<pre>' . $this->esc(print_r($value, true)) . '</pre>'
                : $this->esc((string) $value);
        }

        // --- POST ---
        foreach ($_POST as $name => $value) {
            $data['vars']['post'][$this->esc((string) $name)] = is_array($value)
                ? '<pre>' . $this->esc(print_r($value, true)) . '</pre>'
                : $this->esc((string) $value);
        }

        // --- Request headers ---
        foreach ($this->getRequestHeaders() as $name => $value) {
            $data['vars']['headers'][$this->esc($name)] = $this->esc($value);
        }

        // --- Cookies ---
        foreach ($_COOKIE as $name => $value) {
            $data['vars']['cookies'][$this->esc((string) $name)] = $this->esc((string) $value);
        }

        // --- Request protocol ---
        $isSecure  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
        $proto     = $isSecure ? 'HTTPS' : 'HTTP';
        $serverVer = explode('/', $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1')[1] ?? '1.1';
        $data['vars']['request'] = $proto . '/' . $serverVer;

        // --- Response ---
        $statusCode               = http_response_code() ?: 200;
        $data['vars']['response'] = [
            'statusCode'  => $statusCode,
            'reason'      => $this->getStatusReason($statusCode),
            'contentType' => $this->esc($this->getResponseContentType()),
            'headers'     => [],
        ];
        foreach (headers_list() as $header) {
            $parts = explode(':', $header, 2);
            if (count($parts) === 2) {
                $data['vars']['response']['headers'][$this->esc(trim($parts[0]))] = $this->esc(trim($parts[1]));
            }
        }

        // --- Config tab ---
        $data['config'] = Config::display($this->config);

        return json_encode($data);
    }

    /**
     * Simpan data JSON ke file history, lalu inject script loader ke response.
     *
     * Menggantikan: Toolbar::prepare()
     * Perubahan: tidak ada dependency Services::, tidak ada Kint, tidak ada CSP nonce.
     */
    public function prepare(string &$responseBody, string $contentType = 'text/html'): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        if ($this->hasNativeHeaderConflict()) {
            return;
        }

        if ($this->shouldDisableToolbar()) {
            return;
        }

        if (! str_contains($contentType, 'html')) {
            return;
        }

        $startTime = $this->config['startTime'] ?? $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        $totalTime = microtime(true) - $startTime;
        $data      = $this->run((float) $startTime, $totalTime);

        $historyPath = rtrim($this->config['historyPath'], '/') . '/';
        if (! is_dir($historyPath)) {
            mkdir($historyPath, 0777, true);
        }

        $time     = sprintf('%.6F', microtime(true));
        $filename = $historyPath . 'debugbar_' . $time . '.json';
        file_put_contents($filename, $data);

        $baseURL = rtrim($this->config['baseURL'], '/');
        $script  = PHP_EOL
            . '<script id="debugbar_loader" '
            . 'data-time="' . $time . '" '
            . 'src="' . $baseURL . '?debugbar"></script>'
            . '<script id="debugbar_dynamic_script"></script>'
            . '<style id="debugbar_dynamic_style"></style>'
            . PHP_EOL;

        if (str_contains($responseBody, '<head>')) {
            $responseBody = preg_replace('/<head>/', '<head>' . $script, $responseBody, 1);
        } else {
            $responseBody .= $script;
        }
    }

    /**
     * Tangani request AJAX toolbar (loader JS dan data JSON).
     *
     * Menggantikan: Toolbar::respond()
     * Perubahan: tidak ada CI4 service(), tidak ada helper('security').
     */
    public function respond(): bool
    {
        parse_str($_SERVER['QUERY_STRING'] ?? '', $queryParams);

        // Serve toolbar loader JS
        if (array_key_exists('debugbar', $queryParams)) {
            header('Content-Type: application/javascript');
            $loaderFile = $this->config['viewsPath'] . 'toolbarloader.js';
            $output     = file_exists($loaderFile) ? file_get_contents($loaderFile) : '';
            $output     = str_replace('{url}', rtrim($this->config['baseURL'], '/'), $output);
            echo $output;
            return true;
        }

        // Serve toolbar data untuk waktu request tertentu
        if (! empty($queryParams['debugbar_time'])) {
            $time     = $this->sanitizeFilename((string) $queryParams['debugbar_time']);
            $filepath = rtrim($this->config['historyPath'], '/') . '/debugbar_' . $time . '.json';

            if (is_file($filepath)) {
                $format = $this->negotiateFormat();
                echo $this->format(file_get_contents($filepath), $format);
                return true;
            }

            http_response_code(404);
            return true;
        }

        return false;
    }

    // ---------------------------------------------------------------
    // Rendering
    // ---------------------------------------------------------------

    /**
     * Format dan render toolbar.
     *
     * Menggantikan: Toolbar::format()
     * Perubahan: service('parser') diganti dengan PHP include + extract().
     */
    protected function format(string $jsonData, string $format = 'html'): string
    {
        $data = json_decode($jsonData, true);

        // Tambahkan History collector
        parse_str($_SERVER['QUERY_STRING'] ?? '', $queryParams);
        if (! empty($queryParams['debugbar_time'])
            && preg_match('/\d+\.\d{6}/s', $queryParams['debugbar_time'], $match)
        ) {
            $history = new History($this->config);
            $history->setFiles($match[0], $this->config['maxHistory']);
            $data['collectors'][] = $history->getAsArray();
        }

        if ($format === 'html') {
            $data['styles']           = [];
            $DEBUGBAR_VERSION         = self::VERSION;
            $baseURL                  = rtrim($this->config['baseURL'], '/');
            $viewsPath                = $this->config['viewsPath'];
            $debugBar                 = $this;

            extract($data);

            ob_start();
            include $this->config['viewsPath'] . 'toolbar.tpl.php';
            return (string) ob_get_clean();
        }

        if ($format === 'json') {
            header('Content-Type: application/json');
            return json_encode($data, JSON_PRETTY_PRINT);
        }

        return '';
    }

    /**
     * Render timeline HTML.
     *
     * Identik dengan CI4, dipindahkan ke sini agar bisa dipanggil
     * dari toolbar.tpl.php via $debugBar->renderTimeline().
     */
    public function renderTimeline(
        array $collectors,
        float $startTime,
        int $segmentCount,
        int $segmentDuration,
        array &$styles
    ): string {
        $rows       = $this->collectTimelineData($collectors);
        $styleCount = 0;
        return $this->renderTimelineRecursive($rows, $startTime, $segmentCount, $segmentDuration, $styles, $styleCount);
    }

    protected function renderTimelineRecursive(
        array $rows,
        float $startTime,
        int $segmentCount,
        int $segmentDuration,
        array &$styles,
        int &$styleCount,
        int $level = 0,
        bool $isChild = false
    ): string {
        $displayTime = $segmentCount * $segmentDuration;
        $output      = '';

        foreach ($rows as $row) {
            $hasChildren = isset($row['children']) && ! empty($row['children']);
            $isQuery     = isset($row['query']) && ! empty($row['query']);
            $open        = $row['name'] === 'Controller';

            if ($hasChildren || $isQuery) {
                $output .= '<tr class="timeline-parent' . ($open ? ' timeline-parent-open' : '') . '" id="timeline-' . $styleCount . '_parent" data-toggle="childrows" data-child="timeline-' . $styleCount . '">';
            } else {
                $output .= '<tr>';
            }

            $output .= '<td class="' . ($isChild ? 'debug-bar-width30' : '') . ' debug-bar-level-' . $level . '">'
                . ($hasChildren || $isQuery ? '<nav></nav>' : '') . $row['name'] . '</td>';
            $output .= '<td class="' . ($isChild ? 'debug-bar-width10' : '') . '">' . $row['component'] . '</td>';
            $output .= '<td class="' . ($isChild ? 'debug-bar-width10 ' : '') . 'debug-bar-alignRight">'
                . number_format($row['duration'] * 1000, 2) . ' ms</td>';
            $output .= "<td class='debug-bar-noverflow' colspan='{$segmentCount}'>";

            $offset = ((((float) $row['start'] - $startTime) * 1000) / $displayTime) * 100;
            $length = (((float) $row['duration'] * 1000) / $displayTime) * 100;

            $styles['debug-bar-timeline-' . $styleCount] = "left: {$offset}%; width: {$length}%;";

            $output .= "<span class='timer debug-bar-timeline-{$styleCount}' title='"
                . number_format($length, 2) . "%'></span></td></tr>";

            $styleCount++;

            if ($hasChildren || $isQuery) {
                $output .= '<tr class="child-row' . ($open ? '' : ' debug-bar-ndisplay') . '" id="timeline-' . ($styleCount - 1) . '_children">';
                $output .= '<td colspan="' . ($segmentCount + 3) . '" class="child-container"><table class="timeline"><tbody>';

                if ($isQuery) {
                    $output .= '<tr><td class="query-container debug-bar-level-' . ($level + 1) . '">' . $row['query'] . '</td></tr>';
                } else {
                    $output .= $this->renderTimelineRecursive($row['children'], $startTime, $segmentCount, $segmentDuration, $styles, $styleCount, $level + 1, true);
                }

                $output .= '</tbody></table></td></tr>';
            }
        }

        return $output;
    }

    protected function collectTimelineData(array $collectors): array
    {
        $data = [];
        foreach ($collectors as $collector) {
            if (! $collector['hasTimelineData']) {
                continue;
            }
            $data = array_merge($data, $collector['timelineData']);
        }

        $sortArray = [
            array_column($data, 'start'), SORT_NUMERIC, SORT_ASC,
            array_column($data, 'duration'), SORT_NUMERIC, SORT_DESC,
            &$data,
        ];
        array_multisort(...$sortArray);

        array_walk($data, static function (&$row): void {
            $row['end'] = $row['start'] + $row['duration'];
        });

        return $this->structureTimelineData($data);
    }

    protected function structureTimelineData(array $elements): array
    {
        $element = array_shift($elements);

        while ($elements !== [] && $elements[array_key_first($elements)]['end'] <= $element['end']) {
            $element['children'][] = array_shift($elements);
        }

        if (isset($element['children'])) {
            $element['children'] = $this->structureTimelineData($element['children']);
        }

        if ($elements === []) {
            return [$element];
        }

        return array_merge([$element], $this->structureTimelineData($elements));
    }

    protected function collectVarData(): array
    {
        if (! ($this->config['collectVarData'] ?? true)) {
            return [];
        }

        $data = [];
        foreach ($this->collectors as $collector) {
            if (! $collector->hasVarData()) {
                continue;
            }
            $data = array_merge($data, $collector->getVarData());
        }
        return $data;
    }

    /**
     * Render partial template untuk setiap collector tab.
     *
     * Menggantikan: $parser->setData($display)->render('_database.tpl')
     * Dipanggil dari toolbar.tpl.php.
     */
    public function renderPartial(string $viewsPath, string $template, array $data): string
    {
        extract($data);
        ob_start();
        include $viewsPath . $template;
        return (string) ob_get_clean();
    }

    // ---------------------------------------------------------------
    // Helper methods
    // ---------------------------------------------------------------

    public function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    protected function currentUrl(): string
    {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';
        return $scheme . '://' . $host . $uri;
    }

    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function shouldDisableToolbar(): bool
    {
        $disableOn = $this->config['disableOnHeaders'] ?? ['X-Requested-With' => 'xmlhttprequest'];

        foreach ($disableOn as $headerName => $expectedValue) {
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));
            if (! isset($_SERVER[$serverKey])) {
                continue;
            }
            if ($expectedValue === null) {
                return true;
            }
            if (strtolower($_SERVER[$serverKey]) === strtolower($expectedValue)) {
                return true;
            }
        }

        return false;
    }

    protected function hasNativeHeaderConflict(): bool
    {
        if (headers_sent()) {
            return true;
        }

        foreach (headers_list() as $header) {
            $lower = strtolower($header);
            if (str_starts_with($lower, 'content-type:') && ! str_contains($lower, 'text/html')) {
                return true;
            }
            if (str_starts_with($lower, 'content-disposition:') && str_contains($lower, 'attachment')) {
                return true;
            }
        }

        return false;
    }

    protected function getRequestHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name           = str_replace('_', '-', substr($key, 5));
                $name           = ucwords(strtolower($name), '-');
                $headers[$name] = (string) $value;
            }
        }
        return $headers;
    }

    protected function getResponseContentType(): string
    {
        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                return trim(substr($header, 13));
            }
        }
        return 'text/html';
    }

    protected function getStatusReason(int $code): string
    {
        $reasons = [
            200 => 'OK',        201 => 'Created',           204 => 'No Content',
            301 => 'Moved Permanently', 302 => 'Found',     304 => 'Not Modified',
            400 => 'Bad Request',       401 => 'Unauthorized', 403 => 'Forbidden',
            404 => 'Not Found', 405 => 'Method Not Allowed', 422 => 'Unprocessable Entity',
            500 => 'Internal Server Error', 502 => 'Bad Gateway', 503 => 'Service Unavailable',
        ];
        return $reasons[$code] ?? 'Unknown';
    }

    protected function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    }

    protected function negotiateFormat(): string
    {
        $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
        if (str_contains($accept, 'application/json')) {
            return 'json';
        }
        return 'html';
    }

    protected function roundTo(float $number, int $increments = 5): float
    {
        $increments = 1 / $increments;
        return ceil($number * $increments) / $increments;
    }

    public function reset(): void
    {
        foreach ($this->collectors as $collector) {
            if (method_exists($collector, 'reset')) {
                $collector->reset();
            }
        }
    }
}