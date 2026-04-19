# 📦 Release Notes — Wizdam Debug Toolbar v1.0.0

**Tanggal Rilis:** *19 April 2026*  
**Versi:** `1.0.0`  
**Stability:** Stable  
**License:** MIT  
**PHP Version:** 8.0+ (tested on 8.4)

---

## 🎉 Apa yang Baru di Versi 1.0.0?

Rilis perdana (**v1.0.0**) dari **Wizdam Debug Toolbar** — sebuah debug toolbar standalone yang framework-agnostic, diekstraksi dan direkayasa ulang dari CodeIgniter4 v4.7.2 DebugBar untuk digunakan di luar ekosistem CI4, termasuk aplikasi legacy seperti OJS 2.4.8.5.

### ✨ Fitur Utama

- **Framework Agnostic** — Tidak bergantung pada CI4, Laravel, Slim, atau framework apapun
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
  - 🧱 **BaseCollector** — Abstract base untuk custom collectors

- **2 Mode Integrasi:**
  1. **Output Buffering** — Untuk aplikasi legacy (OJS, Smarty, dll)
  2. **PSR-15 Middleware** — Untuk aplikasi modern dengan middleware stack

- **Adapters Built-in:**
  - `AdodbDatabaseAdapter` — Integrasi native dengan ADODB
  - `WizdamRouterAdapter` — Integrasi dengan Wizdam Router

- **Interfaces untuk Ekstensibilitas:**
  - `DatabaseAdapterInterface` — Untuk custom database adapter
  - `RouterInterface` — Untuk custom router integration
  - `TemplateEngineInterface` — Untuk custom template engine
  - `CollectorInterface` — Untuk membuat collector custom

- **UI Modern:**
  - Dark mode otomatis (mengikuti preferensi sistem)
  - Responsive design
  - AJAX-based history navigation
  - Real-time toolbar injection

- **Assets Lengkap:**
  - CSS (`toolbar.css`) — ~19KB
  - JS Core (`toolbar.js`) — ~29KB
  - JS Loader (`toolbarloader.js`) — ~4KB
  - JS Standalone (`toolbarstandalone.js`) — ~2KB

- **Konfigurasi Fleksibel:**
  - File config PHP (`wizdamtoolbar.php`)
  - Customize max query time, ignored routes, collectors aktif, dll

---

## 📦 Instalasi

### Via Composer (Direkomendasikan)

```bash
composer require wizdamdebug/debug-toolbar
```

### Requirements

| Komponen | Versi Minimum |
|----------|---------------|
| PHP      | 8.0           |
| Composer | 2.x           |

### Optional Dependencies

- `psr/http-message` — Untuk integrasi PSR-7
- `psr/simple-cache` — Untuk PSR-16 history storage

---

## 🏗️ Struktur Package

```
wizdamdebug/debug-toolbar v1.0.0
├── src/
│   ├── DebugToolbar.php              # Main class (~20KB)
│   ├── Middleware/
│   │   └── DebugToolbarMiddleware.php
│   ├── Collectors/                   # 10 collectors
│   ├── Adapters/                     # 2 adapters
│   └── Interfaces/                   # 4 interfaces
├── config/
│   └── wizdamtoolbar.php
├── public/
│   ├── toolbar.css
│   ├── toolbar.js
│   ├── toolbarloader.js
│   └── toolbarstandalone.js
├── views/
│   ├── toolbar.tpl.php
│   └── _*.tpl (7 partial templates)
├── README.md
├── LICENSE (MIT)
└── SECURITY.md
```

**Total Source Files:** 24 PHP files  
**Excluded from Distribution:** 
- `Wizdam_DEPRICATED/` 
- `src_DEPRICATED/`
- Development files (tests, phpunit, psalm, editorconfig, dll)

---

## 🔧 Cara Penggunaan Singkat

### 1. Inisialisasi Dasar

```php
use WizdamDebugToolbar\DebugToolbar;

$toolbar = new DebugToolbar($config);
$toolbar->run();
```

### 2. Integrasi Output Buffering (OJS/Legacy)

```php
ob_start(function ($buffer) {
    $toolbar = new DebugToolbar($config);
    return $toolbar->injectToolbar($buffer);
});
```

### 3. Integrasi PSR-15 Middleware

```php
use WizdamDebugToolbar\Middleware\DebugToolbarMiddleware;

$app->add(new DebugToolbarMiddleware($config));
```

### 4. Custom Database Adapter (ADODB Example)

```php
use WizdamDebugToolbar\Adapters\AdodbDatabaseAdapter;

$dbAdapter = new AdodbDatabaseAdapter($adodbConnection);
$toolbar->setDatabaseAdapter($dbAdapter);
```

Lihat [README.md](https://github.com/sangia/wizdam-debug-toolbar/blob/main/README.md) untuk dokumentasi lengkap.

---

## 🧪 Testing & Kompatibilitas

- ✅ Tested on **PHP 8.4** dengan **OJS 2.4.8.5**
- ✅ Compatible dengan **ADODB**, **Smarty**, dan **legacy codebases**
- ✅ Support **PHP 8.0, 8.1, 8.2, 8.3, 8.4**
- ✅ Namespace PSR-4: `WizdamDebugToolbar\`

---

## 📝 Attribution & License

Library ini berasal dari **CodeIgniter4 v4.7.2 DebugBar** (MIT License) dan telah dimodifikasi secara signifikan untuk penggunaan standalone.

**Copyright:**
- Original: British Columbia Institute of Technology & CodeIgniter Foundation
- Modifications: Sangia Publishing House

**License:** [MIT](https://opensource.org/licenses/MIT)

---

## 📚 Dokumentasi

- **README Lengkap:** https://github.com/sangia/wizdam-debug-toolbar/blob/main/README.md
- **Security Policy:** https://github.com/sangia/wizdam-debug-toolbar/blob/main/SECURITY.md
- **Issue Tracker:** https://github.com/sangia/wizdam-debug-toolbar/issues

---

## 🙏 Terima Kasih Kepada

- **CodeIgniter Foundation** — Untuk DebugBar asli yang menjadi fondasi
- **Community Contributors** — Untuk feedback dan testing
- **Sangia Publishing House** — Untuk pengembangan dan maintenance

---

## 🔮 Rencana Kedepan (v1.1.0+)

- [ ] Support untuk caching collector data
- [ ] Export/import history ke JSON
- [ ] Integration guide untuk Laravel, Symfony, Slim
- [ ] Visual improvements untuk mobile
- [ ] Unit tests coverage > 80%

---

## 📞 Support

Untuk pertanyaan, bug report, atau feature request, silakan buka issue di GitHub atau hubungi:

📧 dev@sangia.org  
🌐 https://www.sangia.org

---

**Happy Debugging! 🐛🔍**
