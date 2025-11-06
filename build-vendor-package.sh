#!/bin/bash
# Vendor Paketi Oluşturma Script'i
# Kullanım: ./build-vendor-package.sh

echo "🎵 Akademik Yayın Görüntüleyicisi - Vendor Paket Oluşturucu"
echo "=========================================================="
echo ""

# Composer kontrol
if ! command -v composer &> /dev/null; then
    echo "❌ Composer bulunamadı!"
    echo "Lütfen composer'ı yükleyin: https://getcomposer.org/download/"
    exit 1
fi

echo "✓ Composer bulundu"
echo ""

# Mevcut vendor klasörünü sil
if [ -d "vendor" ]; then
    echo "🗑️  Mevcut vendor/ klasörü siliniyor..."
    rm -rf vendor/
fi

echo "📦 Composer bağımlılıkları yükleniyor..."
composer install --no-dev --optimize-autoloader --prefer-dist

if [ $? -ne 0 ]; then
    echo "❌ Composer install başarısız!"
    exit 1
fi

echo "✓ Bağımlılıklar yüklendi"
echo ""

# ZIP oluştur
echo "🗜️  vendor.zip oluşturuluyor..."

if command -v zip &> /dev/null; then
    zip -r vendor.zip vendor/ -q
    echo "✓ vendor.zip oluşturuldu (zip komutu ile)"
elif command -v tar &> /dev/null; then
    tar -czf vendor.tar.gz vendor/
    echo "✓ vendor.tar.gz oluşturuldu (tar komutu ile)"
else
    echo "⚠️  ZIP/TAR komutu bulunamadı!"
    echo "vendor/ klasörünü manuel olarak sıkıştırın"
fi

echo ""
echo "🎉 Tamamlandı!"
echo ""
echo "📤 Şimdi ne yapmalısınız?"
echo "1. vendor.zip (veya vendor.tar.gz) dosyasını sunucunuza yükleyin"
echo "2. cPanel File Manager ile dosyayı 'Extract' (Çıkart) edin"
echo "3. check.php ile kontrolü yapın"
echo ""
echo "Dosya boyutu:"
if [ -f "vendor.zip" ]; then
    ls -lh vendor.zip | awk '{print $5}'
elif [ -f "vendor.tar.gz" ]; then
    ls -lh vendor.tar.gz | awk '{print $5}'
fi
