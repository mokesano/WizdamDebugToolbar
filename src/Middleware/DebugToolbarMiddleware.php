<?php

declare(strict_types=1);

namespace WizdamDebugToolbar\Middleware;

use WizdamDebugToolbar\DebugToolbar;

/**
 * DebugToolbarMiddleware
 *
 * Pengganti CI4 Filter/DebugToolbar.php.
 * Menginject HTML toolbar ke dalam response secara otomatis.
 *
 * Mendukung dua mode integrasi:
 *
 * MODE 1 — Output Buffering (untuk OJS / aplikasi tanpa PSR-15)
 * ---------------------------------------------------------------
 *   // Di bootstrap OJS (mis: index.php atau pkp-lib/classes/core/PKPApplication.inc.php)
 *   use WizdamDebugToolbar\Middleware\DebugToolbarMiddleware;
 *
 *   $middleware = new DebugToolbarMiddleware($debugBar);
 *   $middleware->startBuffer();
 *
 *   // ... eksekusi OJS normal ...
 *
 *   $middleware->endBuffer(); // inject toolbar & flush output
 *
 * MODE 2 — PSR-15 (untuk aplikasi modern dengan PSR-7/PSR-15)
 * ---------------------------------------------------------------
 *   // Di stack middleware PSR-15:
 *   $app->add(new DebugToolbarMiddleware($debugBar));
 *
 *   // Implements process(ServerRequestInterface, RequestHandlerInterface)
 * ---
 */
class DebugToolbarMiddleware
{
    private DebugToolbar $debugBar;

    /** Ekstensi Content-Type yang boleh di-inject toolbar */
    private array $allowedContentTypes = [
        'text/html',
        'application/xhtml+xml',
    ];

    public function __construct(DebugToolbar $debugBar)
    {
        $this->debugBar = $debugBar;
    }

    // ---------------------------------------------------------------
    // MODE 1: Output Buffering (untuk OJS)
    // ---------------------------------------------------------------

    /**
     * Mulai menangkap output.
     * Panggil di awal eksekusi aplikasi.
     */
    public function startBuffer(): void
    {
        ob_start();
    }

    /**
     * Ambil output yang sudah ditangkap, inject toolbar, lalu flush.
     * Panggil di akhir eksekusi aplikasi.
     */
    public function endBuffer(): void
    {
        $output = ob_get_clean();

        if ($output === false) {
            return;
        }

        if ($this->shouldInject($output)) {
            $output = $this->inject($output);
        }

        echo $output;
    }

    // ---------------------------------------------------------------
    // MODE 2: PSR-15 (jika framework mendukung)
    // ---------------------------------------------------------------

    /**
     * Process request (PSR-15 style).
     * Dapat digunakan tanpa dependensi PSR-15 formal —
     * cukup panggil secara manual dengan callable handler.
     *
     * @param callable $handler fn(array $request): string — harus return HTML
     */
    public function process(array $request, callable $handler): string
    {
        $response = $handler($request);

        if ($this->shouldInject($response)) {
            $response = $this->inject($response);
        }

        return $response;
    }

    // ---------------------------------------------------------------
    // Core injection
    // ---------------------------------------------------------------

    /**
     * Inject toolbar HTML ke dalam response sebelum tag </body>.
     */
    private function inject(string $response): string
    {
        $toolbarHtml = $this->debugBar->render();

        // Inject tepat sebelum </body> agar tidak mengganggu layout
        if (stripos($response, '</body>') !== false) {
            return str_ireplace('</body>', $toolbarHtml . '</body>', $response);
        }

        // Fallback: tambahkan di akhir jika tidak ada </body>
        return $response . $toolbarHtml;
    }

    /**
     * Cek apakah response layak di-inject.
     * Tidak inject pada AJAX, JSON, binary, atau redirect.
     */
    private function shouldInject(string $response): bool
    {
        // Jangan inject jika bukan request browser
        if ($this->isAjaxRequest()) {
            return false;
        }

        // Jangan inject jika response tidak mengandung HTML
        if (stripos(ltrim($response), '<') !== 0 && stripos($response, '<html') === false) {
            return false;
        }

        // Jangan inject pada Content-Type yang tidak sesuai
        $contentType = $this->getResponseContentType();
        if ($contentType !== null && ! $this->isAllowedContentType($contentType)) {
            return false;
        }

        return true;
    }

    private function isAjaxRequest(): bool
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
    }

    private function getResponseContentType(): ?string
    {
        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                return trim(explode(';', explode(':', $header, 2)[1])[0]);
            }
        }

        return null;
    }

    private function isAllowedContentType(string $contentType): bool
    {
        foreach ($this->allowedContentTypes as $allowed) {
            if (stripos($contentType, $allowed) !== false) {
                return true;
            }
        }

        return false;
    }
}