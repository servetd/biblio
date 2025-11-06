<?php
/**
 * Otomatik Vendor İndirici
 *
 * Bu script composer bağımlılıklarını GitHub'dan otomatik indirir ve kurar.
 * SSH erişimi olmayan paylaşımlı sunucular için ideal!
 *
 * Kullanım: https://yourdomain.com/download-vendor.php
 */

set_time_limit(600); // 10 dakika timeout
ini_set('max_execution_time', 600);

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

class VendorInstaller
{
    private $baseDir;
    private $vendorDir;
    private $logs = [];

    // Gerekli paketler
    private $packages = [
        'renanbr/bibtex-parser' => [
            'version' => '2.2.0',
            'repo' => 'renanbr/bibtex-parser',
            'path' => 'renanbr/bibtex-parser'
        ],
        'phpoffice/phpspreadsheet' => [
            'version' => '1.30.1',
            'repo' => 'PHPOffice/PhpSpreadsheet',
            'path' => 'phpoffice/phpspreadsheet'
        ],
        'psr/simple-cache' => [
            'version' => '3.0.0',
            'repo' => 'php-fig/simple-cache',
            'path' => 'psr/simple-cache'
        ],
        'psr/http-message' => [
            'version' => '2.0',
            'repo' => 'php-fig/http-message',
            'path' => 'psr/http-message'
        ],
        'psr/http-factory' => [
            'version' => '1.1.0',
            'repo' => 'php-fig/http-factory',
            'path' => 'psr/http-factory'
        ],
        'psr/http-client' => [
            'version' => '1.0.3',
            'repo' => 'php-fig/http-client',
            'path' => 'psr/http-client'
        ],
        'markbaker/complex' => [
            'version' => '3.0.2',
            'repo' => 'MarkBaker/PHPComplex',
            'path' => 'markbaker/complex'
        ],
        'markbaker/matrix' => [
            'version' => '3.0.1',
            'repo' => 'MarkBaker/PHPMatrix',
            'path' => 'markbaker/matrix'
        ],
        'maennchen/zipstream-php' => [
            'version' => '3.2.0',
            'repo' => 'maennchen/ZipStream-PHP',
            'path' => 'maennchen/zipstream-php'
        ],
        'ezyang/htmlpurifier' => [
            'version' => 'v4.19.0',
            'repo' => 'ezyang/htmlpurifier',
            'path' => 'ezyang/htmlpurifier'
        ]
    ];

    public function __construct()
    {
        $this->baseDir = __DIR__;
        $this->vendorDir = $this->baseDir . '/vendor';
    }

    public function log($message, $type = 'info')
    {
        $this->logs[] = ['message' => $message, 'type' => $type];
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function checkRequirements()
    {
        $errors = [];

        // ZIP extension kontrolü
        if (!extension_loaded('zip')) {
            $errors[] = 'ZIP extension gerekli ama yüklü değil';
        }

        // allow_url_fopen kontrolü
        if (!ini_get('allow_url_fopen')) {
            $errors[] = 'allow_url_fopen kapalı. Hosting sağlayıcınızdan açmasını isteyin.';
        }

        // Yazma izni kontrolü
        if (!is_writable($this->baseDir)) {
            $errors[] = 'Proje klasörüne yazma izni yok';
        }

        return $errors;
    }

    public function install()
    {
        // Vendor klasörünü oluştur
        if (!is_dir($this->vendorDir)) {
            mkdir($this->vendorDir, 0755, true);
            $this->log('✓ vendor/ klasörü oluşturuldu', 'success');
        } else {
            $this->log('⚠ vendor/ klasörü zaten mevcut', 'warning');
        }

        // Her paketi indir
        foreach ($this->packages as $name => $config) {
            $this->log("📦 İndiriliyor: $name v{$config['version']}", 'info');

            try {
                $this->downloadPackage($config);
                $this->log("✓ Kuruldu: $name", 'success');
            } catch (Exception $e) {
                $this->log("✗ Hata: $name - " . $e->getMessage(), 'error');
                return false;
            }
        }

        // Autoloader dosyalarını oluştur
        $this->createAutoloader();

        $this->log('🎉 Tüm bağımlılıklar başarıyla yüklendi!', 'success');
        return true;
    }

    private function downloadPackage($config)
    {
        $zipUrl = "https://github.com/{$config['repo']}/archive/refs/tags/{$config['version']}.zip";
        $zipFile = $this->vendorDir . '/' . basename($config['path']) . '.zip';
        $extractTo = $this->vendorDir . '/' . dirname($config['path']);

        // Hedef klasörü oluştur
        if (!is_dir($extractTo)) {
            mkdir($extractTo, 0755, true);
        }

        // ZIP dosyasını indir
        $this->log("  → İndiriliyor: $zipUrl", 'info');

        $context = stream_context_create([
            'http' => [
                'timeout' => 120,
                'user_agent' => 'PHP-Vendor-Installer'
            ]
        ]);

        $zipContent = @file_get_contents($zipUrl, false, $context);

        if ($zipContent === false) {
            throw new Exception("İndirme başarısız: $zipUrl");
        }

        file_put_contents($zipFile, $zipContent);
        $this->log("  → İndirildi: " . round(strlen($zipContent) / 1024 / 1024, 2) . " MB", 'info');

        // ZIP'i aç
        $zip = new ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new Exception("ZIP açılamadı: $zipFile");
        }

        // İlk klasör adını al (örn: bibtex-parser-2.2.0)
        $rootFolder = $zip->getNameIndex(0);
        $rootFolderName = trim($rootFolder, '/');

        // Geçici klasöre çıkart
        $tempDir = $this->vendorDir . '/temp_' . uniqid();
        $zip->extractTo($tempDir);
        $zip->close();

        // Dosyaları taşı
        $finalPath = $this->vendorDir . '/' . $config['path'];
        if (is_dir($finalPath)) {
            $this->deleteDirectory($finalPath);
        }

        rename($tempDir . '/' . $rootFolderName, $finalPath);

        // Temizlik
        @unlink($zipFile);
        @rmdir($tempDir);

        $this->log("  → Kuruldu: " . $config['path'], 'success');
    }

    private function createAutoloader()
    {
        $this->log('📝 Autoloader oluşturuluyor...', 'info');

        // composer klasörünü oluştur
        $composerDir = $this->vendorDir . '/composer';
        if (!is_dir($composerDir)) {
            mkdir($composerDir, 0755, true);
        }

        // PSR-4 autoload mapping
        $psr4Map = [
            'App\\' => $this->baseDir . '/src/',
            'RenanBr\\BibTexParser\\' => $this->vendorDir . '/renanbr/bibtex-parser/src/',
            'PhpOffice\\PhpSpreadsheet\\' => $this->vendorDir . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/',
            'Psr\\SimpleCache\\' => $this->vendorDir . '/psr/simple-cache/src/',
            'Psr\\Http\\Message\\' => $this->vendorDir . '/psr/http-message/src/',
            'Psr\\Http\\Client\\' => $this->vendorDir . '/psr/http-client/src/',
            'Complex\\' => $this->vendorDir . '/markbaker/complex/classes/src/',
            'Matrix\\' => $this->vendorDir . '/markbaker/matrix/classes/src/',
            'ZipStream\\' => $this->vendorDir . '/maennchen/zipstream-php/src/',
            'HTMLPurifier' => $this->vendorDir . '/ezyang/htmlpurifier/library/',
        ];

        // autoload_psr4.php
        $psr4Content = "<?php\n\n";
        $psr4Content .= "// autoload_psr4.php @generated by manual installer\n\n";
        $psr4Content .= "\$vendorDir = dirname(__DIR__);\n";
        $psr4Content .= "\$baseDir = dirname(\$vendorDir);\n\n";
        $psr4Content .= "return array(\n";

        foreach ($psr4Map as $namespace => $path) {
            $relativePath = str_replace($this->baseDir, '$baseDir', $path);
            $relativePath = str_replace($this->vendorDir, '$vendorDir', $relativePath);
            $psr4Content .= "    '$namespace' => array($relativePath),\n";
        }

        $psr4Content .= ");\n";

        file_put_contents($composerDir . '/autoload_psr4.php', $psr4Content);

        // autoload_classmap.php
        file_put_contents($composerDir . '/autoload_classmap.php', "<?php\n\nreturn array();\n");

        // autoload_namespaces.php
        file_put_contents($composerDir . '/autoload_namespaces.php', "<?php\n\nreturn array();\n");

        // autoload_files.php
        file_put_contents($composerDir . '/autoload_files.php', "<?php\n\nreturn array();\n");

        // ClassLoader.php (basit versiyon)
        $this->createSimpleClassLoader($composerDir);

        // autoload_real.php
        $this->createAutoloadReal($composerDir);

        // Ana autoload.php
        $autoloadContent = "<?php\n\n";
        $autoloadContent .= "// autoload.php @generated by manual installer\n\n";
        $autoloadContent .= "require_once __DIR__ . '/composer/autoload_real.php';\n\n";
        $autoloadContent .= "return ComposerAutoloaderInit::getLoader();\n";

        file_put_contents($this->vendorDir . '/autoload.php', $autoloadContent);

        $this->log('✓ Autoloader oluşturuldu', 'success');
    }

    private function createSimpleClassLoader($composerDir)
    {
        $classLoaderContent = <<<'PHP'
<?php

namespace Composer\Autoload;

class ClassLoader
{
    private $prefixLengthsPsr4 = array();
    private $prefixDirsPsr4 = array();
    private $fallbackDirsPsr4 = array();

    public function getPrefixes()
    {
        return array();
    }

    public function getPrefixesPsr4()
    {
        return $this->prefixDirsPsr4;
    }

    public function addPsr4($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr4 = (array) $paths;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            if ($prepend) {
                $this->prefixDirsPsr4[$prefix] = (array) $paths + $this->prefixDirsPsr4[$prefix];
            } else {
                $this->prefixDirsPsr4[$prefix] = array_merge(
                    $this->prefixDirsPsr4[$prefix] ?? array(),
                    (array) $paths
                );
            }
        }
    }

    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            includeFile($file);
            return true;
        }
        return false;
    }

    public function findFile($class)
    {
        if (isset($class[0]) && '\\' === $class[0]) {
            $class = substr($class, 1);
        }

        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php';

        $first = $class[0];
        if (isset($this->prefixLengthsPsr4[$first])) {
            foreach ($this->prefixLengthsPsr4[$first] as $prefix => $length) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($this->prefixDirsPsr4[$prefix] as $dir) {
                        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $length))) {
                            return $file;
                        }
                    }
                }
            }
        }

        foreach ($this->fallbackDirsPsr4 as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) {
                return $file;
            }
        }

        return false;
    }
}

function includeFile($file)
{
    include $file;
}

PHP;

        file_put_contents($composerDir . '/ClassLoader.php', $classLoaderContent);
    }

    private function createAutoloadReal($composerDir)
    {
        $autoloadRealContent = <<<'PHP'
<?php

class ComposerAutoloaderInit
{
    private static $loader;

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/ClassLoader.php';

        self::$loader = $loader = new \Composer\Autoload\ClassLoader();

        $map = require __DIR__ . '/autoload_psr4.php';
        foreach ($map as $namespace => $path) {
            $loader->addPsr4($namespace, $path);
        }

        $loader->register(true);

        return $loader;
    }
}

PHP;

        file_put_contents($composerDir . '/autoload_real.php', $autoloadRealContent);
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otomatik Vendor Kurulumu</title>
    <style>
        body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 20px; line-height: 1.6; }
        .container { max-width: 900px; margin: 0 auto; background: #1e293b; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        h1 { color: #3b82f6; margin-bottom: 10px; }
        .subtitle { color: #94a3b8; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 15px 30px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-size: 16px; font-weight: bold; transition: all 0.3s; }
        .btn:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
        .btn:disabled { background: #475569; cursor: not-allowed; transform: none; }
        .log { background: #0f172a; padding: 20px; margin: 20px 0; border-radius: 5px; max-height: 400px; overflow-y: auto; border: 1px solid #334155; }
        .log-item { padding: 8px 0; border-bottom: 1px solid #334155; }
        .log-item:last-child { border-bottom: none; }
        .log-info { color: #60a5fa; }
        .log-success { color: #22c55e; font-weight: bold; }
        .log-error { color: #ef4444; font-weight: bold; }
        .log-warning { color: #f59e0b; }
        .requirements { background: #1e293b; padding: 15px; margin: 20px 0; border-left: 4px solid #3b82f6; border-radius: 3px; }
        .requirements ul { margin: 10px 0 0 20px; }
        .error-box { background: #7f1d1d; border: 2px solid #dc2626; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success-box { background: #14532d; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .progress { width: 100%; height: 30px; background: #334155; border-radius: 15px; overflow: hidden; margin: 20px 0; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #3b82f6, #8b5cf6); transition: width 0.5s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid #334155; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin-right: 10px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .info-card { background: #0f172a; padding: 15px; border-radius: 5px; border: 1px solid #334155; }
        .info-card h3 { color: #3b82f6; margin: 0 0 10px 0; font-size: 14px; }
        .info-card p { margin: 0; font-size: 18px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Otomatik Vendor Kurulumu</h1>
        <p class="subtitle">Composer bağımlılıklarını GitHub'dan otomatik indirin ve kurun</p>

        <?php
        $installer = new VendorInstaller();
        $requirements = $installer->checkRequirements();

        if (!empty($requirements)) {
            echo '<div class="error-box">';
            echo '<h3>⚠️ Gereksinimler Karşılanmadı</h3>';
            echo '<ul>';
            foreach ($requirements as $req) {
                echo '<li>' . htmlspecialchars($req) . '</li>';
            }
            echo '</ul>';
            echo '<p>Lütfen hosting sağlayıcınızla iletişime geçin.</p>';
            echo '</div>';
        } else {
            // Vendor zaten kurulu mu kontrol et
            if (file_exists(__DIR__ . '/vendor/autoload.php')) {
                echo '<div class="success-box">';
                echo '<h3>✓ Vendor Zaten Kurulu!</h3>';
                echo '<p>vendor/autoload.php dosyası bulundu. Bağımlılıklar zaten yüklü.</p>';
                echo '<p><a href="check.php" class="btn">Kurulum Kontrolüne Git →</a></p>';
                echo '</div>';
            } else {
                // Kurulum formu
                if (!isset($_POST['install'])) {
                    ?>
                    <div class="requirements">
                        <h3>📋 Kurulacak Paketler:</h3>
                        <div class="info-grid">
                            <div class="info-card">
                                <h3>Ana Paketler</h3>
                                <p>2</p>
                            </div>
                            <div class="info-card">
                                <h3>Bağımlılıklar</h3>
                                <p>8</p>
                            </div>
                            <div class="info-card">
                                <h3>Toplam</h3>
                                <p>10 paket</p>
                            </div>
                            <div class="info-card">
                                <h3>Süre</h3>
                                <p>~2-5 dk</p>
                            </div>
                        </div>

                        <ul>
                            <li>renanbr/bibtex-parser (BibTeX parser)</li>
                            <li>phpoffice/phpspreadsheet (Excel export)</li>
                            <li>+ 8 PSR ve yardımcı paket</li>
                        </ul>
                    </div>

                    <form method="post" style="text-align: center; margin: 30px 0;">
                        <button type="submit" name="install" class="btn">
                            📦 Bağımlılıkları İndir ve Kur
                        </button>
                    </form>

                    <div class="requirements">
                        <h3>ℹ️ Notlar:</h3>
                        <ul>
                            <li>İşlem 2-5 dakika sürebilir</li>
                            <li>Sayfayı kapatmayın veya yenilemeyin</li>
                            <li>İnternet bağlantınız stabil olmalı</li>
                            <li>GitHub'dan indirilecek: ~10-15 MB</li>
                        </ul>
                    </div>
                    <?php
                } else {
                    // Kurulum yap
                    echo '<div class="log">';
                    echo '<div class="log-item log-info"><span class="spinner"></span>Kurulum başlatılıyor...</div>';
                    echo '</div>';

                    echo '<script>window.scrollTo(0, document.body.scrollHeight);</script>';

                    flush();
                    ob_flush();

                    $success = $installer->install();

                    echo '<div class="log">';
                    foreach ($installer->getLogs() as $log) {
                        $class = 'log-' . $log['type'];
                        echo '<div class="log-item ' . $class . '">' . htmlspecialchars($log['message']) . '</div>';
                    }
                    echo '</div>';

                    if ($success) {
                        echo '<div class="success-box">';
                        echo '<h3>🎉 Kurulum Başarılı!</h3>';
                        echo '<p>Tüm bağımlılıklar başarıyla yüklendi.</p>';
                        echo '<p><a href="check.php" class="btn">Kurulum Kontrolüne Git →</a></p>';
                        echo '<p style="margin-top: 20px;"><small>Bu dosyayı (download-vendor.php) artık silebilirsiniz.</small></p>';
                        echo '</div>';
                    } else {
                        echo '<div class="error-box">';
                        echo '<h3>❌ Kurulum Başarısız</h3>';
                        echo '<p>Bazı paketler indirilemedi. Lütfen logları kontrol edin.</p>';
                        echo '<p><strong>Alternatif:</strong> MANUAL-VENDOR-INSTALL.md dosyasındaki YÖNTEM 1\'i deneyin.</p>';
                        echo '</div>';
                    }
                }
            }
        }
        ?>

        <hr style="border-color: #334155; margin: 40px 0;">

        <div style="text-align: center; color: #64748b; font-size: 14px;">
            <p>Vendor Installer v1.0 | <a href="MANUAL-VENDOR-INSTALL.md" style="color: #3b82f6;">Manuel Kurulum Rehberi</a></p>
        </div>
    </div>
</body>
</html>
