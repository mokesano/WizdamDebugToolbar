<?php

declare(strict_types=1);

namespace WizdamDebugToolbar\Interfaces;

/**
 * CollectorInterface
 *
 * Kontrak dasar yang harus diimplementasi oleh semua collector.
 * Menggantikan BaseCollector CI4 dengan interface yang framework-agnostic.
 */
interface CollectorInterface
{
    /**
     * Nama collector yang ditampilkan di toolbar.
     * Contoh: 'Database', 'Timers', 'Routes'
     */
    public function getTitle(): string;

    /**
     * Data yang dikumpulkan oleh collector ini.
     * Akan di-encode ke JSON dan dikirim ke toolbar via AJAX.
     */
    public function collect(): array;

    /**
     * Apakah collector ini aktif (misalnya: hanya aktif jika koneksi DB tersedia).
     */
    public function isEnabled(): bool;

    /**
     * Label badge yang muncul di toolbar (opsional).
     * Contoh: jumlah query, jumlah file, dsb.
     * Return null jika tidak ingin menampilkan badge.
     */
    public function getBadgeValue(): string|int|null;

    /**
     * Icon untuk tab collector di toolbar.
     * Gunakan nama icon dari set CI4 atau SVG string pendek.
     */
    public function getIcon(): string;
}