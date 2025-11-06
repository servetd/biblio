# ⚡ Hızlı Başlangıç

## 3 Adımda Kurulum

### 1️⃣ Dosyaları Yükleyin
- Tüm dosyaları cPanel File Manager veya FTP ile `public_html/` klasörüne yükleyin

### 2️⃣ Composer Bağımlılıklarını Yükleyin

**Yerel bilgisayarınızda:**
```bash
composer install --no-dev --optimize-autoloader
```
Sonra `vendor/` klasörünü sunucuya yükleyin

**VEYA tarayıcıdan:**
```
https://yourdomain.com/install-composer.php
```

### 3️⃣ İzinleri Ayarlayın

**cPanel File Manager'da:**
- `data` klasörüne sağ tıklayın → Change Permissions → `755`
- `data/uploads` klasörüne sağ tıklayın → Change Permissions → `755`

**SSH ile:**
```bash
chmod 755 data/
chmod 755 data/uploads/
```

## ✅ Kontrol Edin

Tarayıcınızda açın:
```
https://yourdomain.com/check.php
```

Tüm kontroller yeşil ise:
```
https://yourdomain.com/
```

## ❌ Sorun mu var?

### "500 Internal Server Error"
`.htaccess` dosyasını silin ve `.htaccess.alternative` dosyasını `.htaccess` olarak yeniden adlandırın

### "Blank Page" (Beyaz Sayfa)
`check.php` dosyasını açın ve hangi adımın başarısız olduğunu görün

### "Class not found"
`vendor/` klasörü eksik! Adım 2'yi tekrar yapın

### "Database connection failed"
`data/` klasörü izinleri 755 olmalı

## 📖 Detaylı Rehber

Daha fazla bilgi için:
- `INSTALL.md` - Detaylı kurulum rehberi
- `README.md` - Uygulama özellikleri ve kullanım

## 🆘 Yardım

Sorun devam ediyorsa `check.php` çıktısını gönderin.
