# 🧰 Wizdam Debug Toolbar

**Standalone, framework-agnostic debugging toolbar untuk aplikasi PHP. Diekstraksi dan direkayasa ulang dari [CodeIgniter 4 Debug Toolbar](https://github.com/codeigniter4/CodeIgniter4) agar dapat digunakan di luar ekosistem CI4 — termasuk aplikasi legacy seperti OJS 2.4.8.5.**

<p align="center">
  <a href="https://github.com/mokesano/WizdamDebugToolbar"><img src="https://img.shields.io/badge/PHP-^8.0-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version"></a>
  <a href="https://github.com/mokesano/WizdamDebugToolbar/blob/master/LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue?style=for-the-badge" alt="License"></a>
  <a href="https://packagist.org/packages/wizdamdebug/debug-toolbar"><img src="https://img.shields.io/badge/packagist-wizdamdebug%2Fdebug--toolbar-F28D1A?style=for-the-badge&logo=packagist&logoColor=white" alt="Packagist"></a>
  <a href="https://github.com/mokesano/WizdamDebugToolbar/actions"><img src="https://img.shields.io/badge/build-passing-brightgreen?style=for-the-badge&logo=github-actions&logoColor=white" alt="Build Status"></a>
</p>

---

## ✨ Mengapa Wizdam Debug Toolbar?

| Situasi | Solusi |
| :--- | :--- |
| Anda ingin melihat query database yang lambat di aplikasi legacy OJS. | Aktifkan toolbar, dan *DatabaseCollector* akan mencatat semua query ADODB beserta durasinya. |
| Anda penasaran *view* atau *template* mana yang paling lama dirender. | *ViewsCollector* menampilkan daftar view dan waktu rendering masing-masing. |
| Anda perlu tahu rute mana yang *match* dengan request saat ini. | *RoutesCollector* akan menunjukkan rute, *controller*, dan parameter. |
| Anda ingin memantau *event* yang di-*trigger* selama request. | *EventsCollector* menyediakan timeline event. |
| Toolbar harus bekerja tanpa mengganggu framework utama. | Dirancang *framework-agnostic*, bisa diintegrasikan via *output buffering* atau *middleware*. |

---

## 🔧 Instalasi

### Via Composer (Direkomendasikan)

```bash
composer require wizdamdebug/debug-toolbar
```

### Integrasi ke Proyek Development

Tambahkan repository ke `composer.json` proyek Anda:

```bash
composer config repositories.wizdam-debug-toolbar vcs https://github.com/mokesano/WizdamDebugToolbar.git
composer require wizdamdebug/debug-toolbar:@dev
```

> **Dependensi opsional**: `psr/http-message` (untuk integrasi PSR-7) dan `psr/simple-cache` (untuk *history storage*). Tidak ada dependensi wajib lainnya.

---

## ⚡ Contoh Penggunaan

### Inisialisasi Dasar

```php
use WizdamDebugToolbar\DebugToolbar;

$config = require 'config/wizdamtoolbar.php';
$toolbar = new DebugToolbar($config);
$toolbar->run();
```

### Integrasi Aplikasi Legacy (Output Buffering)

Cocok untuk OJS 2.4.8.5 atau aplikasi PHP tanpa *middleware stack*.

```php
define('WIZDAM_DEBUG', true); // Aktifkan hanya di development!

$middleware = new \WizdamDebugToolbar\Middleware\DebugToolbarMiddleware($toolbar);
$middleware->startBuffer();

// ... logika aplikasi Anda berjalan normal ...

$middleware->endBuffer(); // Toolbar otomatis di-inject di akhir HTML
```

### Integrasi Database ADODB

Gunakan `AdodbDatabaseAdapter` untuk mencatat query ADODB:

```php
use WizdamDebugToolbar\Adapters\AdodbDatabaseAdapter;
use WizdamDebugToolbar\Collectors\DatabaseCollector;

$dbAdapter = new AdodbDatabaseAdapter();
$toolbar->addCollector(new DatabaseCollector($dbAdapter));
```

### Integrasi PSR-15 Middleware

Untuk aplikasi modern yang sudah memiliki *middleware stack*:

```php
$app->add(new \WizdamDebugToolbar\Middleware\DebugToolbarMiddleware($toolbar));
```

---

## 🧩 Fitur Utama

| Kategori | Kolektor / Fitur |
| :--- | :--- |
| ⏱️ **Timers** | Benchmark & timeline eksekusi kode |
| 🗄️ **Database** | Query logging (ADODB, PDO, Doctrine) |
| 🛣️ **Routes** | Inspector rute, *controller*, parameter |
| 📄 **Views** | Template *render tracker* dengan durasi |
| 📁 **Files** | Daftar file yang di-*include* / *require* |
| 📡 **Events** | *Event listener* & *trigger tracker* |
| 📋 **Logs** | PSR-3 *log viewer* |
| ⚙️ **Config** | Informasi konfigurasi aplikasi |
| 📜 **History** | Riwayat N *request* terakhir |

---

## ⚙️ Konfigurasi

Salin dan sesuaikan `config/wizdamtoolbar.php`. Beberapa opsi penting:

```php
return [
    'collectors'   => [ /* ... daftar collector class ... */ ],
    'maxHistory'   => 20,
    'historyPath'  => sys_get_temp_dir() . '/wizdam-debug-toolbar/',
    'ignoredRoutes'=> ['/_wizdam-debug-toolbar', '/api/'],
    'toolbarState' => 'minimized',   // 'minimized' atau 'maximized'
    'theme'        => 'auto',        // 'light', 'dark', atau 'auto'
    'maxQueryTime' => 100,           // Highlight query lambat (ms)
];
```

---

## 🧪 Menambah Collector Kustom

Implementasikan `CollectorInterface`:

```php
use WizdamDebugToolbar\Interfaces\CollectorInterface;

class CacheCollector implements CollectorInterface
{
    public function getData(): array { /* ... */ }
    public function isEnabled(): bool { /* ... */ }
    public function getBadgeValue(): string|int|null { /* ... */ }
    public function getIcon(): string { /* ... */ }
}
```

Daftarkan ke toolbar:

```php
$config['collectors'][] = CacheCollector::class;
$toolbar = new DebugToolbar($config);
```

---

## 🔍 Troubleshooting

| Masalah | Solusi |
| :--- | :--- |
| Toolbar tidak muncul | Pastikan `WIZDAM_DEBUG = true` dan *buffer* dimulai sebelum output HTML. |
| Query database tidak tercatat | Pastikan adapter (mis. `AdodbDatabaseAdapter`) sudah didaftarkan ke `DatabaseCollector`. |
| Error "Class not found" | Jalankan `composer dump-autoload` atau periksa konfigurasi *autoloader* manual. |
| History tidak tersimpan | Pastikan direktori `historyPath` *writable* (chmod 755). |

---

## 📄 Lisensi

**MIT License** — Copyright (c) 2025 Sangia Publishing House. Lihat [LICENSE](https://github.com/mokesano/WizdamDebugToolbar/blob/master/LICENSE) untuk teks lengkap.

> Toolbar ini diadaptasi dari [CodeIgniter 4 Debug Toolbar](https://github.com/codeigniter4/CodeIgniter4) (Copyright British Columbia Institute of Technology). Digunakan dengan modifikasi dan izin sesuai lisensi MIT asli.

---

<p align="center">
  <credit>Dibangun dengan ❤️ sebagai bagian dari ekosistem <strong>Wizdam Frontedge</strong> — platform penerbitan ilmiah modern.</credit>
  <br><br>
  <a href="https://github.com/mokesano/WizdamDebugToolbar/stargazers"><img src="https://img.shields.io/github/stars/mokesano/WizdamDebugToolbar?style=social" alt="GitHub Stars"></a>
  <a href="https://github.com/mokesano/WizdamDebugToolbar/network/members"><img src="https://img.shields.io/github/forks/mokesano/WizdamDebugToolbar?style=social" alt="GitHub Forks"></a>
</p>
