<?php

declare(strict_types=1);

/**
 * Wizdam DebugToolbar — konfigurasi default
 *
 * Diadaptasi dari CodeIgniter4 v4.7.2 app/Config/Toolbar.php
 *
 * Perubahan dari versi CI4:
 *   - `extends BaseConfig` dihapus — file ini mengembalikan array PHP biasa
 *   - `SYSTEMPATH` diganti dengan path relatif berbasis __DIR__
 *   - Namespace CI4 collector diganti dengan namespace WizdamDebugToolbar
 *   - Ditambahkan: `baseURL`, `historyPath`, `environment`, `startTime`
 *
 * Cara penggunaan:
 *   $config  = require 'path/to/config/wizdamtoolbar.php';
 *   $debugBar = new \WizdamDebugToolbar\DebugToolbar($config);
 *
 * Override per-instalasi:
 *   $debugBar = new \WizdamDebugToolbar\DebugToolbar([
 *       'baseURL'     => 'https://ojs.example.com',
 *       'historyPath' => '/var/www/ojs/cache/debugbar/',
 *       'maxHistory'  => 10,
 *   ]);
 */

return [

    // ---------------------------------------------------------------
    // Collectors yang aktif
    // ---------------------------------------------------------------
    // Hapus atau komentari collector yang tidak dibutuhkan.
    // Urutan menentukan urutan tab di toolbar.
    'collectors' => [
        \WizdamDebugToolbar\Collectors\Timers::class,
        \WizdamDebugToolbar\Collectors\Database::class,
        \WizdamDebugToolbar\Collectors\Logs::class,
        \WizdamDebugToolbar\Collectors\Views::class,
        \WizdamDebugToolbar\Collectors\Files::class,
        \WizdamDebugToolbar\Collectors\Routes::class,
        \WizdamDebugToolbar\Collectors\Events::class,
    ],

    // ---------------------------------------------------------------
    // Kumpulkan var data dari collector (view data, session, dll)
    // Set false jika aplikasi meneruskan banyak data ke view dan
    // menyebabkan penggunaan memori yang tinggi.
    // ---------------------------------------------------------------
    'collectVarData' => true,

    // ---------------------------------------------------------------
    // Jumlah maksimum request history yang disimpan di disk.
    // 0  = tidak menyimpan history
    // -1 = unlimited
    // ---------------------------------------------------------------
    'maxHistory' => 20,

    // ---------------------------------------------------------------
    // Jumlah maksimum query database yang dicatat.
    // ---------------------------------------------------------------
    'maxQueries' => 100,

    // ---------------------------------------------------------------
    // Path direktori file template toolbar.
    // HARUS diakhiri dengan trailing slash.
    // ---------------------------------------------------------------
    'viewsPath' => __DIR__ . '/../views/',

    // ---------------------------------------------------------------
    // Path penyimpanan file history JSON.
    // Direktori ini harus writable oleh web server.
    // ---------------------------------------------------------------
    'historyPath' => sys_get_temp_dir() . '/wizdam-debugbar/',

    // ---------------------------------------------------------------
    // Base URL aplikasi (tanpa trailing slash).
    // Digunakan untuk membangun URL endpoint toolbar dan asset.
    // ---------------------------------------------------------------
    'baseURL' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/') . '/',

    // ---------------------------------------------------------------
    // Environment saat ini.
    // Otomatis terdeteksi dari APP_ENV jika tidak diisi.
    // ---------------------------------------------------------------
    'environment' => $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'development',

    // ---------------------------------------------------------------
    // Waktu mulai eksekusi aplikasi (microtime float).
    // Jika tidak diisi, diambil dari $_SERVER['REQUEST_TIME_FLOAT'].
    // ---------------------------------------------------------------
    'startTime' => $_SERVER['REQUEST_TIME_FLOAT'] ?? null,

    // ---------------------------------------------------------------
    // Header yang menonaktifkan toolbar secara otomatis.
    // Berguna untuk AJAX, HTMX, Unpoly, Turbo, dsb.
    // Format: ['Header-Name' => 'expected-value'] atau ['Header-Name' => null] (hanya cek keberadaan)
    // ---------------------------------------------------------------
    'disableOnHeaders' => [
        'X-Requested-With' => 'xmlhttprequest',  // AJAX
        'HX-Request'       => 'true',             // HTMX
        'X-Up-Version'     => null,               // Unpoly
    ],

];