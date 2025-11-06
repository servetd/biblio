# Manuel Composer Bağımlılık Yükleme Rehberi

Composer komutunu çalıştıramıyorsanız, bağımlılıkları manuel olarak yüklemek için **3 farklı yöntem** var:

---

## 🎯 YÖNTEM 1: Hazır Vendor Paketi (EN KOLAY)

### Adım 1: Vendor Paketini Oluşturun

Yerel bilgisayarınızda (Windows/Mac/Linux) proje klasöründe:

```bash
# Composer bağımlılıklarını yükle
composer install --no-dev --optimize-autoloader

# vendor klasörünü ZIP'le
zip -r vendor.zip vendor/
```

**Windows'ta** (PowerShell):
```powershell
composer install --no-dev --optimize-autoloader
Compress-Archive -Path vendor -DestinationPath vendor.zip
```

### Adım 2: Sunucuya Yükleyin

1. cPanel → File Manager
2. Proje klasörüne gidin
3. `vendor.zip` dosyasını yükleyin (Upload)
4. `vendor.zip` üzerine sağ tıklayın → "Extract" (Çıkart)
5. `vendor.zip` dosyasını silin

**✓ Tamamlandı!** `check.php` ile kontrol edin.

---

## 🔽 YÖNTEM 2: Otomatik İndirme Script'i (SSH YOK İSE)

Tarayıcıdan bağımlılıkları otomatik indirecek bir script hazırladım.

### Kullanım:

1. `download-vendor.php` dosyasını tarayıcıda açın:
```
https://yourdomain.com/download-vendor.php
```

2. "Bağımlılıkları İndir ve Kur" butonuna tıklayın

3. Script tüm bağımlılıkları GitHub'dan indirecek ve kuracak

4. Tamamlandığında `check.php` ile kontrol edin

---

## 📦 YÖNTEM 3: Manuel İndirme (GitHub'dan)

Her paketi GitHub'dan indirip yerleştirin.

### Gerekli Paketler

#### 1. renanbr/bibtex-parser (v2.2.0)

**İndir:**
```
https://github.com/renanbr/bibtex-parser/archive/refs/tags/2.2.0.zip
```

**Yerleştir:**
```
vendor/renanbr/bibtex-parser/
```

İçeriği:
```
bibtex-parser-2.2.0.zip (açın)
└── bibtex-parser-2.2.0/
    └── src/ ← Bunu kopyalayın

Hedef:
vendor/renanbr/bibtex-parser/src/
```

#### 2. phpoffice/phpspreadsheet (v1.30.1)

**İndir:**
```
https://github.com/PHPOffice/PhpSpreadsheet/archive/refs/tags/1.30.1.zip
```

**Yerleştir:**
```
vendor/phpoffice/phpspreadsheet/
```

#### 3-11. Diğer Bağımlılıklar (PSR paketleri)

Bu paketler küçüktür ve phpspreadsheet ile birlikte gelir:
- psr/simple-cache
- psr/http-message
- psr/http-factory
- psr/http-client
- markbaker/matrix
- markbaker/complex
- maennchen/zipstream-php
- ezyang/htmlpurifier
- composer/pcre

---

## ⚡ En Kolay Yol: download-vendor.php Kullanın

Aşağıdaki script'i kullanarak tüm bağımlılıkları otomatik indirebilirsiniz.

`download-vendor.php` dosyasını proje klasörüne yükleyin ve tarayıcıda açın.

---

## 📋 Manuel Dosya Yapısı

Tamamlandığında dosya yapınız şöyle olmalı:

```
your-project/
├── vendor/
│   ├── autoload.php
│   ├── composer/
│   │   ├── autoload_real.php
│   │   ├── autoload_static.php
│   │   ├── autoload_psr4.php
│   │   ├── autoload_classmap.php
│   │   ├── ClassLoader.php
│   │   └── ...
│   ├── renanbr/
│   │   └── bibtex-parser/
│   │       └── src/
│   ├── phpoffice/
│   │   └── phpspreadsheet/
│   │       └── src/
│   └── psr/
│       ├── simple-cache/
│       ├── http-message/
│       └── ...
├── src/
├── views/
├── public/
├── data/
├── index.php
└── ...
```

---

## ✅ Kontrol

Kurulum sonrası kontrol:

```
https://yourdomain.com/check.php
```

vendor/autoload.php satırı yeşil olmalı ✓

---

## 🆘 Sorun Giderme

### "Class not found" hatası alıyorum

**Sebep:** Autoloader doğru oluşturulmadı

**Çözüm:** YÖNTEM 1 veya YÖNTEM 2'yi kullanın (en güvenli)

### ZIP çıkartamıyorum

**Çözüm 1:** cPanel → File Manager → Extract kullanın

**Çözüm 2:** SSH ile:
```bash
unzip vendor.zip
```

**Çözüm 3:** FTP ile `vendor/` klasörünü olduğu gibi yükleyin (ZIP'siz)

### FTP ile yükleme çok uzun sürüyor

`vendor/` klasöründe 1000+ dosya var, bu normal. Sabırlı olun veya:
- ZIP olarak yükleyip sunucuda açın (daha hızlı)
- YÖNTEM 2'yi kullanın (otomatik indirme)

---

## 💡 Öneriler

1. **En Kolay:** YÖNTEM 2 (download-vendor.php)
2. **En Güvenilir:** YÖNTEM 1 (hazır vendor.zip)
3. **Son Çare:** YÖNTEM 3 (manuel GitHub indirme)

---

Hangi yöntemi tercih ediyorsunuz? Size o yöntem için detaylı adımları verebilirim!
