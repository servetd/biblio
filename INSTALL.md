# Paylaşımlı Server Kurulum Rehberi

## Hızlı Kurulum (3 Adım)

### ADIM 1: Composer Bağımlılıklarını Yükleyin

**Seçenek A - Yerel Bilgisayarınızda (ÖNERİLEN)**

1. Yerel bilgisayarınızda proje klasöründe terminal açın
2. Şu komutu çalıştırın:
```bash
composer install --no-dev --optimize-autoloader
```
3. Oluşan `vendor/` klasörünü FTP/cPanel ile sunucuya yükleyin

**Seçenek B - SSH ile sunucuda**

```bash
cd /path/to/your/project
composer install --no-dev --optimize-autoloader
```

**Seçenek C - Composer yok mu? Manuel indirme**

1. https://github.com/servetd/biblio/releases adresinden vendor.zip indirin
2. Sunucuda proje klasörüne yükleyip açın

### ADIM 2: Klasör İzinlerini Ayarlayın

**cPanel File Manager ile:**
1. `data/` klasörüne sağ tıklayın → "Change Permissions"
2. İzinleri `755` yapın (Read, Write, Execute - Owner)
3. `data/uploads/` klasörü için de aynısını yapın

**SSH ile:**
```bash
chmod 755 data/
chmod 755 data/uploads/
```

### ADIM 3: Test Edin

Tarayıcınızda sitenize gidin: `https://yourdomain.com/`

---

## Sorun Giderme

### ❌ "500 Internal Server Error" Hatası

**Sebep 1: .htaccess sorunu**

`.htaccess` dosyasını şu şekilde güncelleyin:

```apache
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>

Options -Indexes
```

**Sebep 2: PHP ayarları**

`.htaccess` dosyasındaki PHP direktiflerini kaldırın veya yorum satırı yapın:

```apache
# php_flag display_errors Off
# php_value upload_max_filesize 10M
# php_value post_max_size 10M
```

### ❌ "Blank Page" (Beyaz Sayfa)

**Hata mesajlarını açın:**

`index.php` dosyasının en üstüne ekleyin:

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
// ... geri kalan kod
```

### ❌ "Class not found" Hatası

**vendor/ klasörü eksik!**

Composer bağımlılıklarını yükleyin (Adım 1'e dönün)

### ❌ "Failed opening required 'vendor/autoload.php'"

**Çözüm:**

1. `vendor/` klasörünün proje kök dizininde olduğunu kontrol edin
2. Dosya yapısı şöyle olmalı:
```
your-site/
├── vendor/          ← BU OLMALI
├── src/
├── views/
├── public/
├── data/
├── index.php
└── .htaccess
```

### ❌ "Database connection failed"

**Çözüm:**

```bash
# SSH ile
chmod 755 data/

# veya cPanel File Manager'da
# data/ klasörüne 755 izni verin
```

### ❌ "404 Not Found" (Tüm sayfalarda)

**URL rewriting çalışmıyor**

**Test 1:** Direkt index.php ile test edin:
```
https://yourdomain.com/index.php
```

Çalışıyorsa, .htaccess sorunu var.

**Çözüm:** cPanel'de Apache Handlers → "mod_rewrite" aktif mi kontrol edin

**Alternatif:** `.htaccess` dosyasını tamamen kaldırın ve şunu ekleyin:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

---

## Manuel Test

### Test 1: PHP Versiyonu

`test.php` dosyası oluşturun:

```php
<?php
phpinfo();
```

Tarayıcıda açın: `https://yourdomain.com/test.php`
- PHP version 8.1+ olmalı
- SQLite3 etkin olmalı

### Test 2: Dosya İzinleri

```php
<?php
echo "Data klasörü yazılabilir mi? ";
echo is_writable(__DIR__ . '/data') ? 'EVET ✓' : 'HAYIR ✗';
echo "\n<br>";
echo "Uploads klasörü yazılabilir mi? ";
echo is_writable(__DIR__ . '/data/uploads') ? 'EVET ✓' : 'HAYIR ✗';
```

### Test 3: Composer Autoloader

```php
<?php
if (file_exists('vendor/autoload.php')) {
    echo "vendor/autoload.php bulundu ✓\n<br>";
    require 'vendor/autoload.php';
    echo "Autoloader yüklendi ✓\n<br>";
} else {
    echo "vendor/autoload.php BULUNAMADI ✗\n<br>";
    echo "Composer install çalıştırın!";
}
```

---

## cPanel Kurulum (Görsel Adımlar)

### 1. Dosyaları Yükleme

1. cPanel → "File Manager" açın
2. `public_html/` veya `www/` klasörüne gidin
3. "Upload" → Tüm dosyaları yükleyin
4. **ÖNEMLİ:** `vendor/` klasörünü de yükleyin!

### 2. İzin Ayarlama

1. `data` klasörüne sağ tıklayın
2. "Change Permissions" seçin
3. Owner: Read + Write + Execute işaretleyin (755)
4. "Change Permissions" butonuna tıklayın

### 3. PHP Versiyon Kontrolü

1. cPanel → "Select PHP Version"
2. PHP 8.1 veya 8.2 seçin
3. "Extensions" sekmesinde `pdo_sqlite` aktif olmalı

---

## Hızlı Test Dosyası

`check.php` oluşturun ve tarayıcıda açın:

```php
<?php
echo "<h1>Kurulum Kontrolü</h1>";

// PHP Version
echo "<h2>1. PHP Versiyonu</h2>";
echo "Versiyon: " . PHP_VERSION;
echo (version_compare(PHP_VERSION, '8.1.0') >= 0) ? " ✓ OK" : " ✗ Güncelleyin!";
echo "<br><br>";

// Vendor
echo "<h2>2. Composer Vendor</h2>";
echo file_exists('vendor/autoload.php') ? "✓ vendor/autoload.php mevcut" : "✗ vendor/ EKSIK - Composer install gerekli";
echo "<br><br>";

// SQLite
echo "<h2>3. SQLite Desteği</h2>";
echo extension_loaded('pdo_sqlite') ? "✓ PDO SQLite etkin" : "✗ PDO SQLite kapalı";
echo "<br><br>";

// Data klasörü
echo "<h2>4. Klasör İzinleri</h2>";
echo "data/ yazılabilir: " . (is_writable('data') ? "✓ EVET" : "✗ HAYIR - chmod 755 gerekli");
echo "<br>";
echo "data/uploads/ yazılabilir: " . (is_writable('data/uploads') ? "✓ EVET" : "✗ HAYIR - chmod 755 gerekli");
echo "<br><br>";

// Htaccess
echo "<h2>5. .htaccess</h2>";
echo file_exists('.htaccess') ? "✓ .htaccess mevcut" : "✗ .htaccess yok";
echo "<br><br>";

echo "<hr>";
echo "<h2>Sonuç</h2>";
if (
    version_compare(PHP_VERSION, '8.1.0') >= 0 &&
    file_exists('vendor/autoload.php') &&
    extension_loaded('pdo_sqlite') &&
    is_writable('data')
) {
    echo "<p style='color:green; font-size:20px;'>✓ Kurulum hazır! Ana sayfaya gidin.</p>";
} else {
    echo "<p style='color:red; font-size:20px;'>✗ Sorunları yukarıda kontrol edin.</p>";
}
?>
```

---

## Yardım İçin Bana Bildirin

Yukarıdaki kontrol dosyasını çalıştırıp çıktıyı bana gönderin:
- Hangi adımda hata alıyorsunuz?
- `check.php` çıktısı nedir?
- Tarayıcıda ne görüyorsunuz? (Screenshot olabilir)

Ben size özel çözüm sunayım! 🚀
