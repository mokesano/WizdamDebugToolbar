<?php

declare(strict_types=1);

namespace WizdamDebugToolbar\Interfaces;

/**
 * RouterInterface
 *
 * Kontrak untuk mengambil informasi routing dari berbagai framework.
 *
 * Implementasi:
 *   - WizdamRouterAdapter    → untuk OJS 2.4.8.5 ($_REQUEST page/op)
 *   - SlimRouterAdapter    → untuk Slim Framework
 *   - LaravelRouterAdapter → untuk Laravel
 */
interface RouterInterface
{
    /**
     * Route yang cocok dengan request saat ini.
     * Contoh: '/article/view' atau '/index/index'
     */
    public function getCurrentRoute(): string;

    /**
     * Nama controller atau handler yang menangani request.
     * Contoh: 'ArticleHandler', 'IndexHandler'
     */
    public function getController(): string;

    /**
     * Nama method/action yang dieksekusi.
     * Contoh: 'view', 'submit', 'index'
     */
    public function getMethod(): string;

    /**
     * Parameter request (GET/POST) yang diteruskan ke handler.
     * Key-value pair, tanpa parameter routing internal (page/op).
     */
    public function getParams(): array;

    /**
     * HTTP method yang digunakan (GET, POST, PUT, DELETE, dll).
     */
    public function getHttpMethod(): string;
}