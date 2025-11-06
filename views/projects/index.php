<?php
$title = 'Projeler';
ob_start();
?>

<div class="page-header">
    <h2>Projelerim</h2>
    <button class="btn btn-primary" onclick="showCreateModal()">Yeni Proje</button>
</div>

<?php if (empty($projects)): ?>
    <div class="empty-state">
        <p>Henüz proje oluşturmadınız. Başlamak için "Yeni Proje" butonuna tıklayın.</p>
    </div>
<?php else: ?>
    <div class="projects-grid">
        <?php foreach ($projects as $project): ?>
            <div class="project-card">
                <h3><?= htmlspecialchars($project['name']) ?></h3>
                <?php if (!empty($project['description'])): ?>
                    <p class="project-description"><?= htmlspecialchars($project['description']) ?></p>
                <?php endif; ?>

                <div class="project-stats">
                    <span class="stat">
                        <strong><?= $project['publication_count'] ?></strong> yayın
                    </span>
                    <span class="stat">
                        <strong><?= $project['selected_count'] ?></strong> seçili
                    </span>
                </div>

                <?php if (!empty($project['keywords'])): ?>
                    <div class="project-keywords">
                        <small>Anahtar kelimeler: <?= htmlspecialchars($project['keywords']) ?></small>
                    </div>
                <?php endif; ?>

                <div class="project-actions">
                    <a href="/project/view/<?= $project['id'] ?>" class="btn btn-sm btn-primary">Görüntüle</a>
                    <a href="/project/edit/<?= $project['id'] ?>" class="btn btn-sm btn-secondary">Düzenle</a>
                    <button onclick="deleteProject(<?= $project['id'] ?>, '<?= htmlspecialchars($project['name'], ENT_QUOTES) ?>')"
                            class="btn btn-sm btn-danger">Sil</button>
                </div>

                <div class="project-date">
                    <?= date('d.m.Y H:i', strtotime($project['updated_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Create Project Modal -->
<div id="createModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Yeni Proje Oluştur</h3>
            <button class="modal-close" onclick="hideCreateModal()">&times;</button>
        </div>
        <form method="POST" action="/project/create">
            <div class="form-group">
                <label for="name">Proje Adı *</label>
                <input type="text" id="name" name="name" required class="form-control">
            </div>
            <div class="form-group">
                <label for="description">Açıklama</label>
                <textarea id="description" name="description" rows="3" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="keywords">Anahtar Kelimeler</label>
                <input type="text" id="keywords" name="keywords" class="form-control"
                       placeholder="virgül ile ayırın">
                <small>Başlık ve özetlerde otomatik vurgulanacaktır</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideCreateModal()">İptal</button>
                <button type="submit" class="btn btn-primary">Oluştur</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
}

function hideCreateModal() {
    document.getElementById('createModal').style.display = 'none';
}

function deleteProject(id, name) {
    if (confirm(`"${name}" projesini silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`)) {
        window.location.href = '/project/delete/' + id;
    }
}

// Close modal on background click
document.getElementById('createModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideCreateModal();
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
