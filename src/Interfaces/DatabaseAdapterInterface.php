<?php

declare(strict_types=1);

namespace WizdamDebugToolbar\Interfaces;

/**
 * DatabaseAdapterInterface
 *
 * Kontrak untuk menghubungkan DatabaseCollector dengan berbagai
 * driver database: ADODB (OJS), PDO, Doctrine, dll.
 *
 * Implementasi:
 *   - AdodbDatabaseAdapter  → untuk OJS 2.4.8.5 (ADODB)
 *   - PdoDatabaseAdapter    → untuk aplikasi berbasis PDO
 */
interface DatabaseAdapterInterface
{
    /**
     * Daftar semua query yang telah dieksekusi.
     *
     * Setiap entry adalah array dengan key:
     *   - 'sql'      => string   Query SQL yang dijalankan
     *   - 'duration' => float    Waktu eksekusi dalam milidetik
     *   - 'params'   => array    Bind parameter (jika ada)
     *   - 'trace'    => string   Stack trace singkat (opsional)
     */
    public function getQueries(): array;

    /**
     * Total waktu eksekusi semua query dalam milidetik.
     */
    public function getTotalTime(): float;

    /**
     * Query yang dieksekusi lebih dari satu kali (duplikat).
     * Return array of ['sql' => string, 'count' => int].
     */
    public function getDuplicates(): array;

    /**
     * Jumlah total query yang dieksekusi.
     */
    public function getQueryCount(): int;
}