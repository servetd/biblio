@echo off
REM Vendor Paketi Olusturma Script'i (Windows)
REM Kullanim: build-vendor-package.bat

echo.
echo ==========================================================
echo Akademik Yayin Goruntuyuleyicisi - Vendor Paket Olusturucu
echo ==========================================================
echo.

REM Composer kontrol
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo X Composer bulunamadi!
    echo Lutfen composer'i yukleyin: https://getcomposer.org/download/
    pause
    exit /b 1
)

echo + Composer bulundu
echo.

REM Mevcut vendor klasorunu sil
if exist vendor (
    echo Mevcut vendor\ klasoru siliniyor...
    rmdir /s /q vendor
)

echo Composer bagimliliklari yukleniyor...
composer install --no-dev --optimize-autoloader --prefer-dist

if %ERRORLEVEL% NEQ 0 (
    echo X Composer install basarisiz!
    pause
    exit /b 1
)

echo + Bagimliliklar yuklendi
echo.

REM ZIP olustur (PowerShell kullanarak)
echo vendor.zip olusturuluyor...
powershell -command "Compress-Archive -Path vendor -DestinationPath vendor.zip -Force"

if %ERRORLEVEL% EQ 0 (
    echo + vendor.zip olusturuldu
) else (
    echo ! ZIP olusturulamadi. vendor\ klasorunu manuel olarak sikistirin.
)

echo.
echo ==========================================
echo Tamamlandi!
echo ==========================================
echo.
echo Simdi ne yapmalisiniz?
echo 1. vendor.zip dosyasini sunucunuza yukleyin
echo 2. cPanel File Manager ile dosyayi 'Extract' edin
echo 3. check.php ile kontrolu yapin
echo.

REM Dosya boyutunu goster
if exist vendor.zip (
    echo Dosya boyutu:
    dir vendor.zip | find "vendor.zip"
)

echo.
pause
