<?php
$title = 'Proje Düzenle - ' . htmlspecialchars($project['name']);
ob_start();
?>

<div class="page-header">
    <h2>Proje Düzenle</h2>
    <a href="/project/view/<?= $project['id'] ?>" class="btn btn-secondary">Geri Dön</a>
</div>

<div class="form-container">
    <form method="POST" action="/project/edit/<?= $project['id'] ?>">
        <div class="form-group">
            <label for="name">Proje Adı *</label>
            <input type="text" id="name" name="name" required class="form-control"
                   value="<?= htmlspecialchars($project['name']) ?>">
        </div>

        <div class="form-group">
            <label for="description">Açıklama</label>
            <textarea id="description" name="description" rows="3" class="form-control"><?= htmlspecialchars($project['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="keywords">Anahtar Kelimeler</label>
            <input type="text" id="keywords" name="keywords" class="form-control"
                   value="<?= htmlspecialchars($project['keywords']) ?>"
                   placeholder="virgül ile ayırın">
            <small>Başlık ve özetlerde otomatik vurgulanacaktır</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Güncelle</button>
            <a href="/project/view/<?= $project['id'] ?>" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
