<?php

declare(strict_types=1);

namespace WizdamDebugToolbar\Interfaces;

/**
 * TemplateEngineInterface
 *
 * Kontrak untuk mengambil informasi render dari berbagai template engine.
 *
 * Implementasi:
 *   - SmartyTemplateAdapter → untuk OJS 2.4.8.5 (Smarty)
 *   - BladeTemplateAdapter  → untuk Laravel
 *   - TwigTemplateAdapter   → untuk Symfony/Twig
 */
interface TemplateEngineInterface
{
    /**
     * Daftar semua view yang telah di-render.
     *
     * Setiap entry adalah array dengan key:
     *   - 'template' => string  Nama/path file template
     *   - 'duration' => float   Waktu render dalam milidetik
     *   - 'data'     => array   Variabel yang diteruskan ke template
     */
    public function getRenderedViews(): array;

    /**
     * Total waktu render semua view dalam milidetik.
     */
    public function getTotalRenderTime(): float;
}