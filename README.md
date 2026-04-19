# Wizdam Debug Toolbar

**Standalone debug toolbar untuk aplikasi PHP — framework-agnostic, terinspirasi dari CodeIgniter 4 Debug Toolbar.**

Diekstraksi dan direkayasa ulang dari [CodeIgniter4 v4.7.2](https://github.com/codeigniter4/CodeIgniter4) agar dapat digunakan di luar ekosistem CI4 — termasuk aplikasi legacy seperti OJS 2.4.8.5 (ADODB + Smarty + PHP 8.4).

---

## Daftar Isi

- [Fitur](#fitur)
- [Persyaratan](#persyaratan)
- [Instalasi](#instalasi)
- [Struktur Direktori](#struktur-direktori)
- [Cara Penggunaan](#cara-penggunaan)
  - [Integrasi OJS (Output Buffering)](#1-integrasi-ojs-output-buffering)
  - [Integrasi Database ADODB](#2-integrasi-database-adodb)
  - [Integrasi PSR-15 Middleware](#3-integrasi-psr-15-middleware)
- [Collectors](#collectors)
- [Adapters](#adapters)
- [Konfigurasi](#konfigurasi)
- [Menambah Collector Baru](#menambah-collector-baru)
- [Kompatibilitas Framework](#kompatibilitas-framework)
- [Atribusi & Lisensi](#atribusi--lisensi)

---

## Fitur

- **Framework-agnostic** — tidak bergantung pada CI4, Laravel, Slim, atau framework apapun
- **Database query logging** — mendukung ADODB, PDO, dan Doctrine via interface
- **Timeline & benchmark** — visualisasi waktu eksekusi per segmen kode
- **Route inspector** — menampilkan page, op, dan parameter request saat ini
- **View render tracker** — mencatat setiap template yang di-render beserta durasinya
- **Request history** — menyimpan riwayat N request terakhir
- **PSR-3 log viewer** — menampilkan log dari logger apapun yang kompatibel PSR-3
- **Dua mode integrasi** — output buffering (untuk OJS/legacy) dan PSR-15 middleware
- **PHP 8.0–8.4 compatible** — diuji di PHP 8.4 dengan OJS 2.4.8.5
- **Dark mode** — mengikuti preferensi sistem pengguna secara otomatis

---

## Persyaratan

| Komponen | Versi Minimum |
|:---|:---|
| PHP | 8.0 |
| Composer | 2.x |
| Browser | Chrome 90+, Firefox 88+, Safari 14+ |

Tidak ada dependensi Composer yang wajib. Dependensi opsional:
- `psr/http-message` — untuk integrasi PSR-7 request/response
- `psr/simple-cache` — untuk history storage berbasis PSR-16

---

## Instalasi

### Via Composer (direkomendasikan)

```bash
composer require sangia/wizdam-debug-toolbar
```

### Dari repository (development)

```bash
# Tambahkan repository ke composer.json proyek Anda
composer config repositories.wizdam-debugbar vcs https://github.com/sangia/wizdam-debug-toolbar.git

# Install versi development
composer require sangia/wizdam-debug-toolbar:@dev
```

### Manual (tanpa Composer)

1. Download atau clone repository ini
2. Salin folder `src/` ke direktori library proyek Anda
3. Daftarkan namespace `Wizdam\DebugBar\` ke autoloader Anda:

```php
// Di file bootstrap/autoload manual
spl_autoload_register(function (string $class): void {
    $prefix = 'Wizdam\\DebugBar\\';
    $base   = __DIR__ . '/libs/wizdam-debugbar/src/';

    if (str_starts_with($class, $prefix)) {
        $file = $base . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});
```

---

## Struktur Direktori

```
wizdam-debugbar/
├── src/
│   ├── DebugBar.php                    # Engine utama
│   ├── Interfaces/
│   │   ├── CollectorInterface.php      # Kontrak dasar semua collector
│   │   ├── DatabaseAdapterInterface.php # Kontrak database (ADODB/PDO)
│   │   ├── RouterInterface.php          # Kontrak router
│   │   └── TemplateEngineInterface.php  # Kontrak template engine
│   ├── Collectors/
│   │   ├── BaseCollector.php
│   │   ├── DatabaseCollector.php
│   │   ├── RoutesCollector.php
│   │   ├── ViewsCollector.php
│   │   ├── TimersCollector.php
│   │   ├── FilesCollector.php
│   │   ├── LogsCollector.php
│   │   ├── EventsCollector.php
│   │   ├── ConfigCollector.php
│   │   └── HistoryCollector.php
│   ├── Adapters/
│   │   ├── AdodbDatabaseAdapter.php    # Untuk OJS / ADODB
│   │   └── OjsRouterAdapter.php        # Untuk OJS 2.4.8.5
│   └── Middleware/
│       └── DebugToolbarMiddleware.php  # PSR-15 + output buffering
├── public/
│   ├── toolbar.css
│   ├── toolbar.js
│   ├── toolbarloader.js
│   └── toolbarstandalone.js
├── views/
│   ├── toolbar.tpl.php
│   ├── _config.tpl
│   ├── _database.tpl
│   ├── _events.tpl
│   ├── _files.tpl
│   ├── _history.tpl
│   ├── _logs.tpl
│   └── _routes.tpl
├── config/
│   └── debugbar.php
├── composer.json
└── README.md
```

---

## Cara Penggunaan

### 1. Integrasi OJS (Output Buffering)

Cara paling sederhana untuk OJS 2.4.8.5 atau aplikasi PHP legacy apapun.
Tambahkan tiga baris di file bootstrap utama aplikasi Anda (misalnya `index.php`):

```php
<?php

// Di bagian PALING ATAS index.php, sebelum require/include apapun
use Wizdam\DebugBar\DebugBar;
use Wizdam\DebugBar\Middleware\DebugToolbarMiddleware;

// Hanya aktifkan di environment development
if (defined('WIZDAM_DEBUG') && WIZDAM_DEBUG === true) {
    $debugBar   = new DebugBar();
    $middleware = new DebugToolbarMiddleware($debugBar);
    $middleware->startBuffer(); // mulai menangkap output
}

// ... sisa kode bootstrap OJS berjalan normal ...

// Di bagian PALING BAWAH index.php, setelah semua output selesai
if (defined('WIZDAM_DEBUG') && WIZDAM_DEBUG === true) {
    $middleware->endBuffer(); // inject toolbar & flush output
}
```

Definisikan konstanta di file konfigurasi environment Anda:

```php
// config/environment.php atau .env handler
define('WIZDAM_DEBUG', true); // set false di production
```

> **Penting:** Jangan pernah mengaktifkan toolbar di environment production.
> Toolbar menampilkan informasi sensitif seperti query database, konfigurasi server, dan path file.

---

### 2. Integrasi Database ADODB

`AdodbDatabaseAdapter` menggunakan pola static accumulator karena ADODB tidak memiliki event hook bawaan. Ada dua cara mengintegrasikannya:

#### Cara A — Subclass ADOConnection (direkomendasikan)

Buat wrapper tipis di atas koneksi ADODB OJS:

```php
<?php

use Wizdam\DebugBar\Adapters\AdodbDatabaseAdapter;

class WizdamAdodbConnection extends ADOConnection
{
    public function Execute($sql, $inputarr = false)
    {
        $start  = microtime(true);
        $result = parent::Execute($sql, $inputarr);
        $ms     = (microtime(true) - $start) * 1000;

        AdodbDatabaseAdapter::logQuery(
            is_string($sql) ? $sql : (string) $sql,
            $ms,
            is_array($inputarr) ? $inputarr : []
        );

        return $result;
    }
}
```

Kemudian daftarkan adapter ke collector:

```php
use Wizdam\DebugBar\Adapters\AdodbDatabaseAdapter;
use Wizdam\DebugBar\Collectors\DatabaseCollector;

$dbAdapter = new AdodbDatabaseAdapter();
$debugBar->addCollector(new DatabaseCollector($dbAdapter));
```

#### Cara B — Logging manual (untuk kasus khusus)

```php
use Wizdam\DebugBar\Adapters\AdodbDatabaseAdapter;

$start  = microtime(true);
$result = $dbconn->Execute($sql, $params);
$ms     = (microtime(true) - $start) * 1000;

AdodbDatabaseAdapter::logQuery($sql, $ms, $params ?? []);
```

---

### 3. Integrasi PSR-15 Middleware

Untuk aplikasi modern yang sudah memiliki stack middleware PSR-15:

```php
use Wizdam\DebugBar\DebugBar;
use Wizdam\DebugBar\Middleware\DebugToolbarMiddleware;

// Inisialisasi
$debugBar = new DebugBar();

// Tambahkan ke stack middleware PSR-15
$app->add(new DebugToolbarMiddleware($debugBar));
```

Atau gunakan mode `process()` manual:

```php
$middleware = new DebugToolbarMiddleware($debugBar);

$htmlOutput = $middleware->process($_REQUEST, function (array $request): string {
    // handler aplikasi Anda — harus return string HTML
    return $myApp->handle($request);
});

echo $htmlOutput;
```

---

## Collectors

Collector adalah kelas yang mengumpulkan data tertentu untuk ditampilkan di toolbar.

| Collector | Keterangan | Dependency CI4 |
|:---|:---|:---|
| `DatabaseCollector` | Query log, durasi, duplikat | `DatabaseAdapterInterface` |
| `RoutesCollector` | Route saat ini, controller, params | `RouterInterface` |
| `ViewsCollector` | Template yang di-render, durasi render | `TemplateEngineInterface` |
| `TimersCollector` | Benchmark / timeline eksekusi | Tidak ada |
| `FilesCollector` | File yang di-load, penggunaan memori | Tidak ada |
| `LogsCollector` | Output logger (PSR-3 compatible) | Tidak ada |
| `EventsCollector` | Timeline event listener | Tidak ada |
| `ConfigCollector` | Nilai konfigurasi dan ENV vars | Tidak ada |
| `HistoryCollector` | Riwayat N request terakhir | PSR-16 / file storage |

### Menambah atau menonaktifkan collector

```php
use Wizdam\DebugBar\DebugBar;
use Wizdam\DebugBar\Collectors\TimersCollector;
use Wizdam\DebugBar\Collectors\DatabaseCollector;

$debugBar = new DebugBar([
    'collectors' => [
        TimersCollector::class,
        DatabaseCollector::class,
        // tambahkan hanya yang Anda butuhkan
    ],
]);
```

---

## Adapters

Adapter menghubungkan collector dengan implementasi spesifik framework atau library.

### Database

| Adapter | Target | Status |
|:---|:---|:---|
| `AdodbDatabaseAdapter` | OJS 2.4.8.5 / ADODB | ✅ Tersedia |
| `PdoDatabaseAdapter` | Aplikasi berbasis PDO | 🔧 Planned |
| `DoctrineAdapter` | Symfony / Doctrine ORM | 🔧 Planned |

### Router

| Adapter | Target | Status |
|:---|:---|:---|
| `OjsRouterAdapter` | OJS 2.4.8.5 (`$_REQUEST['page'/'op']`) | ✅ Tersedia |
| `SlimRouterAdapter` | Slim Framework 4 | 🔧 Planned |
| `LaravelRouterAdapter` | Laravel 10+ | 🔧 Planned |

### Membuat adapter sendiri

Implementasikan interface yang sesuai:

```php
<?php

use Wizdam\DebugBar\Interfaces\DatabaseAdapterInterface;

class MyCustomDatabaseAdapter implements DatabaseAdapterInterface
{
    public function getQueries(): array
    {
        // kembalikan daftar query yang sudah dieksekusi
        return MyDatabase::getQueryLog();
    }

    public function getTotalTime(): float
    {
        return MyDatabase::getTotalQueryTime();
    }

    public function getQueryCount(): int
    {
        return count($this->getQueries());
    }

    public function getDuplicates(): array
    {
        // deteksi query yang dijalankan lebih dari satu kali
        $counts = array_count_values(
            array_column($this->getQueries(), 'sql')
        );
        return array_filter($counts, fn($c) => $c > 1);
    }
}
```

---

## Konfigurasi

Salin dan sesuaikan file `config/debugbar.php`:

```php
<?php

return [
    // Collector yang aktif
    'collectors' => [
        \Wizdam\DebugBar\Collectors\TimersCollector::class,
        \Wizdam\DebugBar\Collectors\DatabaseCollector::class,
        \Wizdam\DebugBar\Collectors\RoutesCollector::class,
        \Wizdam\DebugBar\Collectors\FilesCollector::class,
        \Wizdam\DebugBar\Collectors\LogsCollector::class,
        \Wizdam\DebugBar\Collectors\HistoryCollector::class,
    ],

    // Jumlah maksimum riwayat request yang disimpan
    'maxHistory' => 20,

    // Direktori penyimpanan file history (harus writable)
    'historyPath' => sys_get_temp_dir() . '/wizdam-debugbar/',

    // Route yang tidak di-inject toolbar (regex pattern)
    'ignoredRoutes' => [
        '/_wizdam-debugbar',
        '/api/',
    ],

    // Tampilan awal toolbar ('minimized' atau 'maximized')
    'toolbarState' => 'minimized',

    // Tema toolbar ('light', 'dark', atau 'auto')
    'theme' => 'auto',
];
```

---

## Menambah Collector Baru

Buat class yang mengimplementasikan `CollectorInterface`:

```php
<?php

namespace MyApp\Debug\Collectors;

use Wizdam\DebugBar\Interfaces\CollectorInterface;

class CacheCollector implements CollectorInterface
{
    public function getTitle(): string
    {
        return 'Cache';
    }

    public function collect(): array
    {
        return [
            'hits'   => MyCacheDriver::getHits(),
            'misses' => MyCacheDriver::getMisses(),
        ];
    }

    public function isEnabled(): bool
    {
        return class_exists('MyCacheDriver');
    }

    public function getBadgeValue(): string|int|null
    {
        return MyCacheDriver::getHits() . ' hits';
    }

    public function getIcon(): string
    {
        return 'cache'; // nama icon dari set toolbar
    }
}
```

Daftarkan ke DebugBar:

```php
$debugBar->addCollector(new CacheCollector());
```

---

## Kompatibilitas Framework

| Framework / Platform | Versi | Status | Adapter tersedia |
|:---|:---|:---|:---|
| OJS (Open Journal Systems) | 2.4.8.5 | ✅ Diuji | Database, Router |
| PHP Native / Custom | 8.0–8.4 | ✅ Diuji | — |
| Slim Framework | 4.x | 🔧 Planned | — |
| Laravel | 10, 11 | 🔧 Planned | — |
| Symfony | 6, 7 | 🔧 Planned | — |
| CodeIgniter 3 | 3.1.x | 🔧 Planned | — |

---

## Atribusi & Lisensi

Wizdam Frontedge DebugBar diekstraksi dan direkayasa ulang dari **CodeIgniter4 v4.7.2 Debug Toolbar**, yang dikembangkan oleh [CodeIgniter Foundation](https://codeigniter.com) dan kontributornya.

File-file berikut diadaptasi dari CodeIgniter4 (dengan modifikasi namespace dan penghapusan dependency framework):
- `src/Collectors/` — berdasarkan `system/Debug/Toolbar/Collectors/`
- `views/` — berdasarkan `system/Debug/Toolbar/Views/`
- `public/` — berdasarkan `system/Debug/Toolbar/Views/` (CSS, JS)

File-file berikut dibuat baru dan tidak berasal dari CodeIgniter4:
- `src/Interfaces/` — seluruh interface
- `src/Adapters/` — seluruh adapter
- `src/Middleware/DebugToolbarMiddleware.php`

---

Lisensi: **MIT**

Copyright (c) 2017 [Sangia Publishing House](https://www.sangia.org)
Copyright (c) 2014-2024 British Columbia Institute of Technology (CodeIgniter Foundation)

Lihat file [LICENSE](LICENSE) untuk teks lisensi lengkap.

---

*Dikembangkan sebagai bagian dari ekosistem **Wizdam Frontedge** — platform penerbitan ilmiah berbasis OJS dengan arsitektur modern.*