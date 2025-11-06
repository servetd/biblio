# Akademik Yayın Görüntüleyicisi

Akademik Yayın Görüntüleyicisi, araştırmalarınızı proje bazında düzenlemenizi ve hızlıca taramanızı sağlayan bir web uygulamasıdır.

## Özellikler

✅ **Proje Yönetimi**: Araştırmalarınızı proje bazında organize edin
✅ **BibTeX/RIS Desteği**: Birden çok BibTeX veya RIS dosyası yükleyin
✅ **Detaylı Görüntüleme**: Başlık, yazar, kaynak, DOI ve özet bilgilerini tek ekranda görün
✅ **Klavye Kısayolları**: ← → ok tuşları ile gezinin, Space ile seçin
✅ **Yayın İşaretleme**: Uygun gördüğünüz yayınları kolayca seçili olarak işaretleyin
✅ **Özet Yazı Boyutu**: İhtiyacınıza göre özet yazı boyutunu ayarlayın
✅ **Anahtar Kelime Vurgulama**: Proje düzeyinde belirlediğiniz anahtar kelimeler otomatik vurgulanır
✅ **Excel Export**: Seçili yayınları kaynakça bilgileriyle Excel olarak indirin

## Gereksinimler

- PHP 8.1 veya üzeri
- SQLite desteği (varsayılan olarak PHP ile gelir)
- Apache web server (mod_rewrite etkin)
- Composer (kurulum için)

## Kurulum

### 1. Dosyaları Yükleyin

Tüm dosyaları paylaşımlı sunucunuzun public_html (veya www) klasörüne yükleyin.

### 2. Composer Bağımlılıklarını Yükleyin

Sunucunuzda SSH erişimi varsa:

```bash
composer install --no-dev --optimize-autoloader
```

SSH erişiminiz yoksa, yerel bilgisayarınızda:

```bash
composer install --no-dev --optimize-autoloader
```

Sonra `vendor/` klasörünü sunucuya yükleyin.

### 3. Klasör İzinlerini Ayarlayın

`data/` klasörünün yazılabilir olduğundan emin olun:

```bash
chmod 755 data/
chmod 755 data/uploads/
```

### 4. .htaccess Kontrolü

Apache'de mod_rewrite etkin değilse, sunucu yöneticinizle iletişime geçin veya hosting kontrol panelinizden etkinleştirin.

### 5. Uygulamayı Açın

Tarayıcınızdan sitenize gidin. Veritabanı otomatik olarak oluşturulacaktır.

## Kullanım

### Proje Oluşturma

1. Ana sayfada **"Yeni Proje"** butonuna tıklayın
2. Proje adını, açıklamasını ve anahtar kelimeleri girin
3. Anahtar kelimeler virgülle ayrılmalıdır (örn: `machine learning, artificial intelligence, deep learning`)

### Dosya Yükleme

1. Bir projeyi açın
2. **"Dosya Yükle"** butonuna tıklayın
3. Bir veya birden fazla .bib veya .ris dosyası seçin
4. Dosyalar otomatik olarak işlenecek ve yayınlar listeye eklenecektir

### Yayınları Görüntüleme

- **Klavye ile Gezinme**:
  - `←` veya `↑` : Önceki yayın
  - `→` veya `↓` : Sonraki yayın
  - `Space` : Aktif yayını seç/seçimi kaldır

- **Özet Boyutu**:
  - `+` ve `-` butonları ile özet yazı boyutunu ayarlayın

- **Yayın Seçme**:
  - Her yayının sağ üst köşesindeki "Seç" butonu ile seçebilirsiniz
  - Veya Space tuşu ile aktif yayını seçebilirsiniz

### Excel Dışa Aktarma

1. Dışa aktarmak istediğiniz yayınları seçin
2. **"Excel İndir"** butonuna tıklayın
3. Seçili tüm yayınlar kaynakça bilgileriyle birlikte Excel dosyası olarak indirilecektir

## Desteklenen Dosya Formatları

### BibTeX (.bib)

Standart BibTeX formatındaki tüm entry tipleri desteklenir:
- @article
- @book
- @inproceedings
- @phdthesis
- @techreport
- ve diğerleri

### RIS (.ris)

Standart RIS formatı desteklenir. Yaygın etiketler:
- TY (Type of reference)
- TI (Title)
- AU (Author)
- PY (Publication year)
- JO (Journal)
- AB (Abstract)
- DO (DOI)
- UR (URL)

## Dizin Yapısı

```
/
├── index.php              # Ana router
├── .htaccess              # URL rewriting kuralları
├── composer.json          # PHP bağımlılıkları
├── README.md              # Bu dosya
├── config/
│   └── database.php       # Veritabanı yapılandırması
├── src/
│   ├── Database.php       # Veritabanı yönetimi
│   ├── Project.php        # Proje modeli
│   ├── Publication.php    # Yayın modeli
│   ├── Parser/
│   │   ├── BibTeXParser.php
│   │   └── RISParser.php
│   └── Exporter/
│       └── ExcelExporter.php
├── views/
│   ├── layout.php         # Ana şablon
│   └── projects/
│       ├── index.php      # Proje listesi
│       ├── edit.php       # Proje düzenleme
│       └── view.php       # Yayın görüntüleyici
├── public/
│   ├── css/
│   │   └── style.css      # Stil dosyası
│   └── js/
│       └── app.js         # JavaScript dosyası
└── data/
    ├── database.sqlite    # SQLite veritabanı (otomatik oluşturulur)
    └── uploads/           # Yüklenen dosyalar
```

## Paylaşımlı Server Uyumluluğu

Bu uygulama özellikle paylaşımlı sunucular için tasarlanmıştır:

- ✅ MySQL/MariaDB gerektirmez (SQLite kullanır)
- ✅ Shell erişimi gerektirmez
- ✅ Minimal PHP uzantıları (PDO SQLite, varsayılan olarak gelir)
- ✅ .htaccess ile routing (cPanel uyumlu)
- ✅ Dosya tabanlı veritabanı
- ✅ Düşük kaynak tüketimi

## Güvenlik

- SQL injection koruması (PDO prepared statements)
- XSS koruması (htmlspecialchars)
- CSRF koruması için session kullanımı
- Dosya yükleme validasyonu (.bib ve .ris uzantıları)
- Dizin listeleme engelleme

## Sorun Giderme

### .htaccess Çalışmıyor

`httpd.conf` dosyanızda `AllowOverride All` ayarını kontrol edin veya hosting sağlayıcınıza danışın.

### Veritabanı Oluşturulamıyor

`data/` klasörünün yazma izinlerine sahip olduğundan emin olun:

```bash
chmod 755 data/
```

### Composer Yüklenemedi

Yerel bilgisayarınızda composer install yapıp `vendor/` klasörünü FTP ile yükleyin.

### Dosya Yüklenemiyor

PHP ayarlarınızı kontrol edin:
- `upload_max_filesize = 10M`
- `post_max_size = 10M`

## Teknik Detaylar

- **PHP Version**: 8.1+
- **Database**: SQLite 3
- **Libraries**:
  - renanbr/bibtex-parser (BibTeX parsing)
  - phpoffice/phpspreadsheet (Excel export)
- **Frontend**: Vanilla JavaScript, Custom CSS
- **Architecture**: MVC-like pattern

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## Destek

Sorularınız veya önerileriniz için issue açabilirsiniz.

---

**Geliştirici Notu**: Bu uygulama akademik araştırmacılar için geliştirilmiştir ve tamamen ücretsizdir.
