<?php
/**
 * Otomatik Composer Kurulum Script'i
 *
 * Bu dosyayı tarayıcınızda açın: https://yourdomain.com/install-composer.php
 *
 * SSH erişiminiz yoksa bu script composer bağımlılıklarını sizin için yükleyecektir.
 */

set_time_limit(300); // 5 dakika timeout

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Composer Kurulum</title>
    <style>
        body { font-family: monospace; background: #1e293b; color: #e2e8f0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #0f172a; padding: 30px; border-radius: 10px; }
        h1 { color: #3b82f6; }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .info { color: #3b82f6; }
        .command { background: #374151; padding: 10px; margin: 10px 0; border-radius: 5px; }
        pre { background: #1e293b; padding: 15px; overflow-x: auto; border-left: 4px solid #3b82f6; }
        .btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; border: none; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #2563eb; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎵 Composer Bağımlılık Kurulumu</h1>

        <?php
        $vendorExists = is_dir(__DIR__ . '/vendor');
        $composerJson = __DIR__ . '/composer.json';

        if ($vendorExists): ?>
            <div class="step">
                <p class="success">✓ vendor/ klasörü zaten mevcut!</p>
                <p>Composer bağımlılıkları yüklenmiş görünüyor.</p>
                <p><a href="check.php" class="btn">Kurulum Kontrolüne Git →</a></p>
            </div>
        <?php else: ?>
            <div class="step">
                <p class="warning">⚠️ vendor/ klasörü bulunamadı.</p>
                <p>Composer bağımlılıklarını yüklemek için aşağıdaki seçeneklerden birini kullanın:</p>
            </div>

            <h2>Seçenek 1: Yerel Bilgisayarınızda (ÖNERİLEN)</h2>
            <div class="step">
                <p><strong>1.</strong> Yerel bilgisayarınızda bu proje klasöründe terminal açın</p>
                <div class="command">
                    composer install --no-dev --optimize-autoloader
                </div>

                <p><strong>2.</strong> Oluşan <code>vendor/</code> klasörünü FTP/cPanel ile sunucuya yükleyin</p>

                <p class="info">Bu yöntem en hızlı ve güvenilir yöntemdir.</p>
            </div>

            <h2>Seçenek 2: cPanel Terminal (SSH)</h2>
            <div class="step">
                <p><strong>1.</strong> cPanel → Terminal açın</p>
                <p><strong>2.</strong> Proje klasörüne gidin:</p>
                <div class="command">
                    cd ~/public_html/
                </div>

                <p><strong>3.</strong> Composer'ı çalıştırın:</p>
                <div class="command">
                    composer install --no-dev --optimize-autoloader
                </div>

                <?php if (function_exists('shell_exec')): ?>
                    <form method="post" style="margin-top: 20px;">
                        <button type="submit" name="auto_install" class="btn">
                            🚀 Otomatik Yükle (SSH ile)
                        </button>
                    </form>
                <?php else: ?>
                    <p class="error">⚠️ shell_exec() fonksiyonu devre dışı. Manuel yükleme yapmalısınız.</p>
                <?php endif; ?>
            </div>

            <?php
            if (isset($_POST['auto_install']) && function_exists('shell_exec')) {
                echo '<h2>Kurulum Çıktısı:</h2>';
                echo '<pre>';

                // Composer'ın kurulu olup olmadığını kontrol et
                $composerPath = trim(shell_exec('which composer 2>/dev/null'));

                if (empty($composerPath)) {
                    echo "❌ Composer bulunamadı. Manuel kurulum yapmalısınız.\n";
                } else {
                    echo "✓ Composer bulundu: $composerPath\n\n";
                    echo "Bağımlılıklar yükleniyor...\n\n";

                    $output = shell_exec("cd " . escapeshellarg(__DIR__) . " && composer install --no-dev --optimize-autoloader 2>&1");
                    echo htmlspecialchars($output);

                    if (is_dir(__DIR__ . '/vendor')) {
                        echo "\n\n<span class='success'>✓ Kurulum başarılı!</span>\n";
                        echo "<a href='check.php' class='btn' style='display:inline-block; margin-top:20px;'>Kurulum Kontrolüne Git →</a>";
                    } else {
                        echo "\n\n<span class='error'>❌ Kurulum başarısız. Manuel yükleme gerekli.</span>\n";
                    }
                }

                echo '</pre>';
            }
            ?>

            <h2>Seçenek 3: Manuel İndirme</h2>
            <div class="step">
                <p><strong>1.</strong> Aşağıdaki dosyaları indirin:</p>
                <ul>
                    <li>renanbr/bibtex-parser</li>
                    <li>phpoffice/phpspreadsheet</li>
                </ul>

                <p><strong>2.</strong> <code>vendor/</code> klasörü oluşturun ve dosyaları yerleştirin</p>

                <p class="warning">⚠️ Bu yöntem zordur ve önerilmez.</p>
            </div>

            <h2>Alternatif: composer.phar Kullanın</h2>
            <div class="step">
                <p>Eğer sunucunuzda composer kurulu değilse:</p>

                <p><strong>1.</strong> composer.phar'ı indirin:</p>
                <div class="command">
                    curl -sS https://getcomposer.org/installer | php
                </div>

                <p><strong>2.</strong> Bağımlılıkları yükleyin:</p>
                <div class="command">
                    php composer.phar install --no-dev --optimize-autoloader
                </div>

                <?php if (function_exists('shell_exec')): ?>
                    <form method="post" style="margin-top: 20px;">
                        <button type="submit" name="download_composer" class="btn">
                            📥 composer.phar İndir ve Yükle
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php
            if (isset($_POST['download_composer']) && function_exists('shell_exec')) {
                echo '<h2>composer.phar Kurulum:</h2>';
                echo '<pre>';

                // composer.phar indir
                echo "composer.phar indiriliyor...\n";
                $composerInstaller = file_get_contents('https://getcomposer.org/installer');

                if ($composerInstaller) {
                    file_put_contents(__DIR__ . '/composer-setup.php', $composerInstaller);
                    $output = shell_exec("cd " . escapeshellarg(__DIR__) . " && php composer-setup.php 2>&1");
                    echo htmlspecialchars($output) . "\n";

                    if (file_exists(__DIR__ . '/composer.phar')) {
                        echo "\n✓ composer.phar indirildi!\n\n";
                        echo "Bağımlılıklar yükleniyor...\n\n";

                        $output = shell_exec("cd " . escapeshellarg(__DIR__) . " && php composer.phar install --no-dev --optimize-autoloader 2>&1");
                        echo htmlspecialchars($output);

                        // Temizlik
                        @unlink(__DIR__ . '/composer-setup.php');
                        @unlink(__DIR__ . '/composer.phar');

                        if (is_dir(__DIR__ . '/vendor')) {
                            echo "\n\n<span class='success'>✓ Kurulum başarılı!</span>\n";
                            echo "<a href='check.php' class='btn' style='display:inline-block; margin-top:20px;'>Kurulum Kontrolüne Git →</a>";
                        }
                    } else {
                        echo "\n❌ composer.phar indirilemedi.\n";
                    }
                } else {
                    echo "❌ Composer installer indirilemedi. İnternet bağlantınızı kontrol edin.\n";
                }

                echo '</pre>';
            }
            ?>

        <?php endif; ?>

        <hr style="margin: 40px 0; border-color: #475569;">

        <h2>Yardım</h2>
        <div class="step">
            <p><strong>Hala sorun mu yaşıyorsunuz?</strong></p>
            <ol>
                <li><a href="check.php" style="color: #3b82f6;">check.php</a> dosyasını açın ve hangi adımın başarısız olduğunu kontrol edin</li>
                <li>INSTALL.md dosyasını okuyun</li>
                <li>Hosting sağlayıcınızdan SSH erişimi isteyin</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 40px; color: #64748b;">
            <p>Composer Kurulum Script v1.0</p>
        </div>
    </div>
</body>
</html>
