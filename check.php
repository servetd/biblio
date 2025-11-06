<?php
/**
 * Kurulum Kontrol Dosyası
 * Bu dosyayı tarayıcınızda açın: https://yourdomain.com/check.php
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurulum Kontrolü</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h1 { color: #2563eb; border-bottom: 3px solid #2563eb; padding-bottom: 10px; }
        .check-item { background: #f8fafc; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #94a3b8; }
        .check-item.success { border-left-color: #16a34a; background: #dcfce7; }
        .check-item.error { border-left-color: #dc2626; background: #fee2e2; }
        .check-item h3 { margin: 0 0 10px 0; }
        .status { font-size: 24px; margin-right: 10px; }
        .result { font-size: 20px; padding: 20px; text-align: center; border-radius: 5px; margin-top: 20px; }
        .result.success { background: #dcfce7; color: #166534; }
        .result.error { background: #fee2e2; color: #991b1b; }
        .info { background: #fff; border: 1px solid #e2e8f0; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .code { background: #1e293b; color: #e2e8f0; padding: 10px; border-radius: 3px; overflow-x: auto; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>📋 Akademik Yayın Görüntüleyicisi - Kurulum Kontrolü</h1>

    <?php
    $errors = [];
    $warnings = [];

    // 1. PHP Versiyonu
    $phpVersion = PHP_VERSION;
    $phpOk = version_compare($phpVersion, '8.1.0', '>=');
    ?>

    <div class="check-item <?= $phpOk ? 'success' : 'error' ?>">
        <h3><span class="status"><?= $phpOk ? '✓' : '✗' ?></span>1. PHP Versiyonu</h3>
        <div class="info">
            <strong>Mevcut:</strong> <?= $phpVersion ?><br>
            <strong>Gerekli:</strong> 8.1 veya üzeri<br>
            <strong>Durum:</strong> <?= $phpOk ? 'UYGUN ✓' : 'UYGUN DEĞİL ✗' ?>
        </div>
        <?php if (!$phpOk): ?>
            <?php $errors[] = 'PHP versiyonunu 8.1 veya üzerine yükseltin (cPanel → Select PHP Version)'; ?>
            <div class="code">cPanel → Select PHP Version → PHP 8.1 veya 8.2 seçin</div>
        <?php endif; ?>
    </div>

    <?php
    // 2. Vendor klasörü
    $vendorExists = file_exists(__DIR__ . '/vendor/autoload.php');
    ?>

    <div class="check-item <?= $vendorExists ? 'success' : 'error' ?>">
        <h3><span class="status"><?= $vendorExists ? '✓' : '✗' ?></span>2. Composer Bağımlılıkları</h3>
        <div class="info">
            <strong>vendor/autoload.php:</strong> <?= $vendorExists ? 'Mevcut ✓' : 'BULUNAMADI ✗' ?><br>
            <strong>Durum:</strong> <?= $vendorExists ? 'YÜKLENDİ' : 'YÜKLENMEDI' ?>
        </div>
        <?php if (!$vendorExists): ?>
            <?php $errors[] = 'Composer bağımlılıkları yüklenmedi'; ?>
            <p><strong>ÇÖZÜM:</strong> Yerel bilgisayarınızda şu komutu çalıştırın:</p>
            <div class="code">composer install --no-dev --optimize-autoloader</div>
            <p>Sonra <code>vendor/</code> klasörünü FTP ile sunucuya yükleyin.</p>
        <?php endif; ?>
    </div>

    <?php
    // 3. SQLite desteği
    $sqliteOk = extension_loaded('pdo_sqlite');
    ?>

    <div class="check-item <?= $sqliteOk ? 'success' : 'error' ?>">
        <h3><span class="status"><?= $sqliteOk ? '✓' : '✗' ?></span>3. SQLite Desteği</h3>
        <div class="info">
            <strong>PDO SQLite Extension:</strong> <?= $sqliteOk ? 'Etkin ✓' : 'KAPALI ✗' ?>
        </div>
        <?php if (!$sqliteOk): ?>
            <?php $errors[] = 'PDO SQLite extension etkin değil'; ?>
            <div class="code">cPanel → Select PHP Version → Extensions → pdo_sqlite işaretleyin</div>
        <?php endif; ?>
    </div>

    <?php
    // 4. Data klasörü izinleri
    $dataExists = is_dir(__DIR__ . '/data');
    $dataWritable = is_writable(__DIR__ . '/data');
    $uploadsWritable = is_writable(__DIR__ . '/data/uploads');
    ?>

    <div class="check-item <?= ($dataWritable && $uploadsWritable) ? 'success' : 'error' ?>">
        <h3><span class="status"><?= ($dataWritable && $uploadsWritable) ? '✓' : '✗' ?></span>4. Klasör İzinleri</h3>
        <div class="info">
            <strong>data/ klasörü mevcut:</strong> <?= $dataExists ? 'EVET ✓' : 'HAYIR ✗' ?><br>
            <strong>data/ yazılabilir:</strong> <?= $dataWritable ? 'EVET ✓' : 'HAYIR ✗' ?><br>
            <strong>data/uploads/ yazılabilir:</strong> <?= $uploadsWritable ? 'EVET ✓' : 'HAYIR ✗' ?>
        </div>
        <?php if (!$dataWritable || !$uploadsWritable): ?>
            <?php $errors[] = 'Klasör izinleri hatalı'; ?>
            <p><strong>ÇÖZÜM:</strong></p>
            <div class="code">
                # SSH ile:<br>
                chmod 755 data/<br>
                chmod 755 data/uploads/
            </div>
            <p>veya cPanel File Manager'da klasöre sağ tıklayın → Change Permissions → 755 yapın</p>
        <?php endif; ?>
    </div>

    <?php
    // 5. .htaccess kontrolü
    $htaccessExists = file_exists(__DIR__ . '/.htaccess');
    ?>

    <div class="check-item <?= $htaccessExists ? 'success' : 'error' ?>">
        <h3><span class="status"><?= $htaccessExists ? '✓' : '✗' ?></span>5. URL Rewriting (.htaccess)</h3>
        <div class="info">
            <strong>.htaccess dosyası:</strong> <?= $htaccessExists ? 'Mevcut ✓' : 'YOK ✗' ?>
        </div>
        <?php if (!$htaccessExists): ?>
            <?php $warnings[] = '.htaccess dosyası bulunamadı'; ?>
            <p>.htaccess dosyasını oluşturun veya gizli dosyaları göster seçeneğini açın</p>
        <?php endif; ?>
    </div>

    <?php
    // 6. Config klasörü
    $configExists = file_exists(__DIR__ . '/config/database.php');
    ?>

    <div class="check-item <?= $configExists ? 'success' : 'error' ?>">
        <h3><span class="status"><?= $configExists ? '✓' : '✗' ?></span>6. Yapılandırma Dosyaları</h3>
        <div class="info">
            <strong>config/database.php:</strong> <?= $configExists ? 'Mevcut ✓' : 'YOK ✗' ?>
        </div>
        <?php if (!$configExists): ?>
            <?php $errors[] = 'Config dosyaları eksik'; ?>
        <?php endif; ?>
    </div>

    <?php
    // 7. Autoloader test
    if ($vendorExists) {
        try {
            require_once __DIR__ . '/vendor/autoload.php';
            $autoloaderOk = true;
        } catch (Exception $e) {
            $autoloaderOk = false;
            $autoloaderError = $e->getMessage();
        }
    } else {
        $autoloaderOk = false;
    }
    ?>

    <div class="check-item <?= $autoloaderOk ? 'success' : 'error' ?>">
        <h3><span class="status"><?= $autoloaderOk ? '✓' : '✗' ?></span>7. Autoloader Testi</h3>
        <div class="info">
            <strong>Durum:</strong> <?= $autoloaderOk ? 'Çalışıyor ✓' : 'HATA ✗' ?>
        </div>
        <?php if (!$autoloaderOk && isset($autoloaderError)): ?>
            <div class="code"><?= htmlspecialchars($autoloaderError) ?></div>
        <?php endif; ?>
    </div>

    <!-- SONUÇ -->
    <hr>

    <?php
    $allOk = $phpOk && $vendorExists && $sqliteOk && $dataWritable && $uploadsWritable && $configExists && $autoloaderOk;
    ?>

    <?php if ($allOk): ?>
        <div class="result success">
            <h2>🎉 Kurulum Tamamlandı!</h2>
            <p>Tüm kontroller başarılı. Uygulamayı kullanmaya başlayabilirsiniz.</p>
            <p><a href="/" style="color: #2563eb; font-size: 18px; font-weight: bold;">Ana Sayfaya Git →</a></p>
            <p style="margin-top: 20px; font-size: 14px; color: #666;">
                <strong>Güvenlik:</strong> Bu check.php dosyasını silmeyi unutmayın!
            </p>
        </div>
    <?php else: ?>
        <div class="result error">
            <h2>⚠️ Kurulum Tamamlanamadı</h2>
            <p>Aşağıdaki sorunları düzeltin:</p>
            <ul style="text-align: left; display: inline-block;">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
            <h3>🚀 Hızlı Çözüm Adımları:</h3>
            <ol>
                <li>Yerel bilgisayarınızda: <code>composer install --no-dev</code></li>
                <li><code>vendor/</code> klasörünü FTP ile sunucuya yükleyin</li>
                <li>cPanel File Manager → <code>data</code> klasörü → Change Permissions → <strong>755</strong></li>
                <li>Bu sayfayı yenileyin (F5)</li>
            </ol>
        </div>
    <?php endif; ?>

    <!-- Sistem Bilgileri -->
    <div style="margin-top: 30px; padding: 15px; background: #f1f5f9; border-radius: 5px;">
        <h3>💻 Sistem Bilgileri</h3>
        <table style="width: 100%; font-size: 14px;">
            <tr>
                <td><strong>PHP Version:</strong></td>
                <td><?= PHP_VERSION ?></td>
            </tr>
            <tr>
                <td><strong>Server Software:</strong></td>
                <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td><strong>Document Root:</strong></td>
                <td><?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td><strong>Script Filename:</strong></td>
                <td><?= __FILE__ ?></td>
            </tr>
            <tr>
                <td><strong>Upload Max Filesize:</strong></td>
                <td><?= ini_get('upload_max_filesize') ?></td>
            </tr>
            <tr>
                <td><strong>Post Max Size:</strong></td>
                <td><?= ini_get('post_max_size') ?></td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin-top: 30px; color: #64748b; font-size: 14px;">
        <p>Kurulum Kontrol v1.0 | Akademik Yayın Görüntüleyicisi</p>
    </div>

</body>
</html>
