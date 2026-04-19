# Wizdam Debug Toolbar

**Standalone debug toolbar untuk aplikasi PHP — framework-agnostic, terinspirasi dari CodeIgniter 4 Debug Toolbar.**

Diekstraksi dan direkayasa ulang dari [CodeIgniter4 v4.7.2](https://github.com/codeigniter4/CodeIgniter4) agar dapat digunakan di luar ekosistem CI4 — termasuk aplikasi legacy seperti OJS 2.4.8.5 (ADODB + Smarty + PHP 8.4).

![PHP Version](https://img.shields.io/badge/php-8.0+-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Version](https://img.shields.io/badge/version-1.0.0-orange.svg)

---

## Daftar Isi

- [Fitur](#fitur)
- [Persyaratan](#persyaratan)
- [Instalasi](#instalasi)
- [Struktur Direktori](#struktur-direktori)
- [Cara Penggunaan](#cara-penggunaan)
  - [Inisialisasi Dasar](#inisialisasi-dasar)
  - [Integrasi OJS (Output Buffering)](#1-integrasi-ojs-output-buffering)
  - [Integrasi Database ADODB](#2-integrasi-database-adodb)
  - [Integrasi PSR-15 Middleware](#3-integrasi-psr-15-middleware)
- [Collectors](#collectors)
- [Adapters](#adapters)
- [Konfigurasi](#konfigurasi)
- [Menambah Collector Baru](#menambah-collector-baru)
- [Kompatibilitas Framework](#kompatibilitas-framework)
- [Troubleshooting](#troubleshooting)
- [Atribusi & Lisensi](#atribusi--lisensi)

---

## Fitur

- **Framework-agnostic** — tidak bergantung pada CI4, Laravel, Slim, atau framework apapun
- **10 Collectors Built-in:**
  - ⏱️ **Timers** — Benchmark & timeline eksekusi kode
  - 💾 **Database** — Query logging dengan support ADODB, PDO, Doctrine
  - 🛣️ **Routes** — Inspector route (page, op, parameters)
  - 📄 **Views** — Template render tracker dengan durasi
  - 📁 **Files** — Daftar file yang di-include/required
  - 🔥 **Events** — Event listener & trigger tracker
  - 📝 **Logs** — PSR-3 log viewer
  - ⚙️ **Config** — Informasi konfigurasi aplikasi
  - 📜 **History** — Riwayat N request terakhir
- **Dua mode integrasi:**
  - Output buffering (untuk OJS/legacy)
  - PSR-15 middleware (untuk aplikasi modern)
- **Adapters built-in:**
  - `AdodbDatabaseAdapter` — Integrasi native dengan ADODB
  - `WizdamRouterAdapter` — Integrasi dengan Wizdam Router
- **Interfaces untuk ekstensibilitas:**
  - `DatabaseAdapterInterface`, `RouterInterface`, `TemplateEngineInterface`, `CollectorInterface`
- **UI Modern:**
  - Dark mode otomatis (mengikuti preferensi sistem)
  - Responsive design
  - AJAX-based history navigation
  - Real-time toolbar injection
- **PHP 8.0–8.4 compatible** — diuji di PHP 8.4 dengan OJS 2.4.8.5

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
composer require wizdamdebug/debug-toolbar
```

### Dari repository (development)

```bash
# Tambahkan repository ke composer.json proyek Anda
composer config repositories.wizdam-debug-toolbar vcs https://github.com/sangia/wizdam-debug-toolbar.git

# Install versi development
composer require wizdamdebug/debug-toolbar:@dev
```

### Manual (tanpa Composer)

1. Download atau clone repository ini
2. Salin folder `src/`, `config/`, `public/`, dan `views/` ke direktori library proyek Anda
3. Daftarkan namespace `WizdamDebugToolbar\` ke autoloader Anda:

```php
// Di file bootstrap/autoload manual
spl_autoload_register(function (string $class): void {
    $prefix = 'WizdamDebugToolbar\\';
    $base   = __DIR__ . '/libs/wizdam-debug-toolbar/src/';

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
wizdam-debug-toolbar/
├── src/
│   ├── DebugToolbar.php                    # Engine utama (~20KB)
│   ├── Middleware/
│   │   └── DebugToolbarMiddleware.php      # PSR-15 + output buffering
│   ├── Collectors/                         # 10 collectors
│   │   ├── BaseCollector.php
│   │   ├── TimersCollector.php
│   │   ├── DatabaseCollector.php
│   │   ├── RoutesCollector.php
│   │   ├── ViewsCollector.php
│   │   ├── FilesCollector.php
│   │   ├── EventsCollector.php
│   │   ├── LogsCollector.php
│   │   ├── ConfigCollector.php
│   │   └── HistoryCollector.php
│   ├── Adapters/                           # 2 adapters
│   │   ├── AdodbDatabaseAdapter.php        # Untuk OJS / ADODB
│   │   └── WizdamRouterAdapter.php         # Untuk Wizdam Router
│   └── Interfaces/                         # 4 interfaces
│       ├── CollectorInterface.php
│       ├── DatabaseAdapterInterface.php
│       ├── RouterInterface.php
│       └── TemplateEngineInterface.php
├── config/
│   └── wizdamtoolbar.php                   # File konfigurasi
├── public/
│   ├── toolbar.css                         # ~19KB
│   ├── toolbar.js                          # ~29KB
│   ├── toolbarloader.js                    # ~4KB
│   └── toolbarstandalone.js                # ~2KB
├── views/
│   ├── toolbar.tpl.php                     # Main template
│   ├── _config.tpl
│   ├── _database.tpl
│   ├── _events.tpl
│   ├── _files.tpl
│   ├── _history.tpl
│   ├── _logs.tpl
│   └── _routes.tpl
├── composer.json
├── README.md
├── LICENSE
└── SECURITY.md
```

**Catatan:** Folder `Wizdam_DEPRICATED/` dan `src_DEPRICATED/` tidak termasuk dalam distribusi package.

---

## Cara Penggunaan

### Inisialisasi Dasar

```php
use WizdamDebugToolbar\DebugToolbar;

$config = require 'config/wizdamtoolbar.php';
$toolbar = new DebugToolbar($config);
$toolbar->run();
```

### 1. Integrasi OJS (Output Buffering)

Cara paling sederhana untuk OJS 2.4.8.5 atau aplikasi PHP legacy apapun.
Tambahkan tiga baris di file bootstrap utama aplikasi Anda (misalnya `index.php`):

```php
<?php

// Di bagian PALING ATAS index.php, sebelum require/include apapun
use WizdamDebugToolbar\DebugToolbar;
use WizdamDebugToolbar\Middleware\DebugToolbarMiddleware;

// Hanya aktifkan di environment development
if (defined('WIZDAM_DEBUG') && WIZDAM_DEBUG === true) {
    $config     = require 'config/wizdamtoolbar.php';
    $toolbar    = new DebugToolbar($config);
    $middleware = new DebugToolbarMiddleware($toolbar);
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

use WizdamDebugToolbar\Adapters\AdodbDatabaseAdapter;

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
use WizdamDebugToolbar\Adapters\AdodbDatabaseAdapter;
use WizdamDebugToolbar\Collectors\DatabaseCollector;

$dbAdapter = new AdodbDatabaseAdapter();
$toolbar->addCollector(new DatabaseCollector($dbAdapter));
```

#### Cara B — Logging manual (untuk kasus khusus)

```php
use WizdamDebugToolbar\Adapters\AdodbDatabaseAdapter;

$start  = microtime(true);
$result = $dbconn->Execute($sql, $params);
$ms     = (microtime(true) - $start) * 1000;

AdodbDatabaseAdapter::logQuery($sql, $ms, $params ?? []);
```

---

### 3. Integrasi PSR-15 Middleware

Untuk aplikasi modern yang sudah memiliki stack middleware PSR-15:

```php
use WizdamDebugToolbar\DebugToolbar;
use WizdamDebugToolbar\Middleware\DebugToolbarMiddleware;

// Inisialisasi
$config  = require 'config/wizdamtoolbar.php';
$toolbar = new DebugToolbar($config);

// Tambahkan ke stack middleware PSR-15
$app->add(new DebugToolbarMiddleware($toolbar));
```

Atau gunakan mode `process()` manual:

```php
$config   = require 'config/wizdamtoolbar.php';
$toolbar  = new DebugToolbar($config);
$middleware = new DebugToolbarMiddleware($toolbar);

$htmlOutput = $middleware->process($_REQUEST, function (array $request): string {
    // handler aplikasi Anda — harus return string HTML
    return $myApp->handle($request);
});

echo $htmlOutput;
```

---

## Collectors

Collector adalah kelas yang mengumpulkan data tertentu untuk ditampilkan di toolbar.

| Collector | Keterangan | Dependency |
|:---|:---|:---|
| `TimersCollector` | Benchmark / timeline eksekusi | Tidak ada |
| `DatabaseCollector` | Query log, durasi, duplikat | `DatabaseAdapterInterface` |
| `RoutesCollector` | Route saat ini, controller, params | `RouterInterface` |
| `ViewsCollector` | Template yang di-render, durasi render | `TemplateEngineInterface` |
| `FilesCollector` | File yang di-load, penggunaan memori | Tidak ada |
| `EventsCollector` | Timeline event listener | Tidak ada |
| `LogsCollector` | Output logger (PSR-3 compatible) | Tidak ada |
| `ConfigCollector` | Nilai konfigurasi dan ENV vars | Tidak ada |
| `HistoryCollector` | Riwayat N request terakhir | PSR-16 / file storage |

### Menambah atau menonaktifkan collector

```php
use WizdamDebugToolbar\DebugToolbar;
use WizdamDebugToolbar\Collectors\TimersCollector;
use WizdamDebugToolbar\Collectors\DatabaseCollector;

$config = [
    'collectors' => [
        TimersCollector::class,
        DatabaseCollector::class,
        // tambahkan hanya yang Anda butuhkan
    ],
];

$toolbar = new DebugToolbar($config);
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
| `WizdamRouterAdapter` | Wizdam Router | ✅ Tersedia |
| `SlimRouterAdapter` | Slim Framework 4 | 🔧 Planned |
| `LaravelRouterAdapter` | Laravel 10+ | 🔧 Planned |

### Membuat adapter sendiri

Implementasikan interface yang sesuai:

```php
<?php

namespace MyApp\Adapters;

use WizdamDebugToolbar\Interfaces\DatabaseAdapterInterface;

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

Salin dan sesuaikan file `config/wizdamtoolbar.php`:

```php
<?php

return [
    // Collector yang aktif
    'collectors' => [
        \WizdamDebugToolbar\Collectors\TimersCollector::class,
        \WizdamDebugToolbar\Collectors\DatabaseCollector::class,
        \WizdamDebugToolbar\Collectors\RoutesCollector::class,
        \WizdamDebugToolbar\Collectors\FilesCollector::class,
        \WizdamDebugToolbar\Collectors\EventsCollector::class,
        \WizdamDebugToolbar\Collectors\LogsCollector::class,
        \WizdamDebugToolbar\Collectors\ConfigCollector::class,
        \WizdamDebugToolbar\Collectors\HistoryCollector::class,
    ],

    // Jumlah maksimum riwayat request yang disimpan
    'maxHistory' => 20,

    // Direktori penyimpanan file history (harus writable)
    'historyPath' => sys_get_temp_dir() . '/wizdam-debug-toolbar/',

    // Route yang tidak di-inject toolbar (regex pattern)
    'ignoredRoutes' => [
        '/_wizdam-debug-toolbar',
        '/api/',
    ],

    // Tampilan awal toolbar ('minimized' atau 'maximized')
    'toolbarState' => 'minimized',

    // Tema toolbar ('light', 'dark', atau 'auto')
    'theme' => 'auto',
    
    // Max query time untuk highlighting (ms)
    'maxQueryTime' => 100,
];
```

---

## Menambah Collector Baru

Buat class yang mengimplementasikan `CollectorInterface`:

```php
<?php

namespace MyApp\Collectors;

use WizdamDebugToolbar\Interfaces\CollectorInterface;

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

Daftarkan ke toolbar:

```php
use WizdamDebugToolbar\DebugToolbar;
use MyApp\Collectors\CacheCollector;

$config = require 'config/wizdamtoolbar.php';
$config['collectors'][] = CacheCollector::class;

$toolbar = new DebugToolbar($config);
```

---

## Troubleshooting

### Toolbar tidak muncul

1. **Pastikan `WIZDAM_DEBUG` didefinisikan sebagai `true`**
   ```php
   define('WIZDAM_DEBUG', true);
   ```

2. **Cek apakah output buffering aktif**
   Pastikan `startBuffer()` dipanggil sebelum ada output HTML dan `endBuffer()` dipanggil setelah semua output selesai.

3. **Periksa ignored routes**
   Jika URL Anda match dengan pattern di `ignoredRoutes`, toolbar tidak akan di-inject.

4. **Cek browser console untuk error JavaScript**
   Tekan F12 > Console dan cari error terkait `toolbar.js`.

### Query database tidak tercatat

1. **Pastikan adapter sudah didaftarkan**
   ```php
   $dbAdapter = new AdodbDatabaseAdapter();
   $toolbar->addCollector(new DatabaseCollector($dbAdapter));
   ```

2. **Untuk ADODB: Pastikan wrapper class digunakan**
   Gunakan `WizdamAdodbConnection` atau logging manual via `AdodbDatabaseAdapter::logQuery()`.

### Error "Class not found"

Pastikan autoloader sudah dikonfigurasi dengan benar untuk namespace `WizdamDebugToolbar\`:

```php
spl_autoload_register(function (string $class): void {
    $prefix = 'WizdamDebugToolbar\\';
    // ... sesuaikan path ke folder src/
});
```

Atau gunakan Composer autoloading:
```bash
composer dump-autoload
```

### History tidak tersimpan

1. **Pastikan direktori `historyPath` writable**
   ```php
   'historyPath' => sys_get_temp_dir() . '/wizdam-debug-toolbar/',
   ```
   
2. **Cek permission folder**
   ```bash
   chmod 755 /tmp/wizdam-debug-toolbar
   ```

---

## Kompatibilitas Framework

| Framework / Platform | Versi | Status | Adapter tersedia |
|:---|:---|:---|:---|
| OJS (Open Journal Systems) | 2.4.8.5 | ✅ Diuji | Database, Router |
| PHP Native / Custom | 8.0–8.4 | ✅ Diuji | — |
| Wizdam Router | Latest | ✅ Diuji | Router |
| Slim Framework | 4.x | 🔧 Planned | — |
| Laravel | 10, 11 | 🔧 Planned | — |
| Symfony | 6, 7 | 🔧 Planned | — |
| CodeIgniter 3 | 3.1.x | 🔧 Planned | — |

---

## Atribusi & Lisensi

**Wizdam Debug Toolbar** diekstraksi dan direkayasa ulang dari **CodeIgniter4 v4.7.2 Debug Toolbar**, yang dikembangkan oleh [CodeIgniter Foundation](https://codeigniter.com) dan kontributornya.

### File yang diadaptasi dari CodeIgniter4:
- `src/Collectors/` — berdasarkan `system/Debug/Toolbar/Collectors/`
- `views/` — berdasarkan `system/Debug/Toolbar/Views/`
- `public/` — berdasarkan `system/Debug/Toolbar/` (CSS, JS)

Semua file tersebut telah dimodifikasi dengan:
- Perubahan namespace dari `CodeIgniter\Debug\Toolbar` ke `WizdamDebugToolbar`
- Penghapusan dependency framework CI4
- Adaptasi untuk standalone usage

### File yang dibuat baru (tidak berasal dari CodeIgniter4):
- `src/Interfaces/` — seluruh interface (`CollectorInterface`, `DatabaseAdapterInterface`, `RouterInterface`, `TemplateEngineInterface`)
- `src/Adapters/` — seluruh adapter (`AdodbDatabaseAdapter`, `WizdamRouterAdapter`)
- `src/Middleware/DebugToolbarMiddleware.php`
- `src/DebugToolbar.php` — main engine yang direkayasa ulang
- `config/wizdamtoolbar.php` — konfigurasi standalone

---

### Lisensi

**MIT License**

Copyright (c) 2025 [Sangia Publishing House](https://www.sangia.org)  
Copyright (c) 2014-2024 British Columbia Institute of Technology (CodeIgniter Foundation)

Lihat file [LICENSE](LICENSE) untuk teks lisensi lengkap.

---

## Support & Kontribusi

Untuk pertanyaan, bug report, atau feature request:

- 📧 Email: dev@sangia.org
- 🐛 Issue Tracker: https://github.com/sangia/wizdam-debug-toolbar/issues
- 📖 Dokumentasi: README.md ini

Kontribusi sangat diterima! Silakan fork repository dan buat pull request.

---

*Dikembangkan sebagai bagian dari ekosistem **Wizdam Frontedge** — platform penerbitan ilmiah berbasis OJS dengan arsitektur modern.*

**Happy Debugging! 🐛🔍**