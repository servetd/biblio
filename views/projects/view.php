<?php
$title = htmlspecialchars($project['name']) . ' - Yayınlar';
$selectedCount = count(array_filter($publications, fn($p) => $p['is_selected'] == 1));
ob_start();
?>

<div class="page-header">
    <div>
        <h2><?= htmlspecialchars($project['name']) ?></h2>
        <?php if (!empty($project['description'])): ?>
            <p class="project-subtitle"><?= htmlspecialchars($project['description']) ?></p>
        <?php endif; ?>
    </div>
    <div class="header-actions">
        <a href="/" class="btn btn-secondary">Projelere Dön</a>
        <a href="/project/edit/<?= $project['id'] ?>" class="btn btn-secondary">Düzenle</a>
        <button class="btn btn-primary" onclick="showUploadModal()">Dosya Yükle</button>
        <?php if ($selectedCount > 0): ?>
            <a href="/export/<?= $project['id'] ?>" class="btn btn-success">Excel İndir (<?= $selectedCount ?>)</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($project['keywords'])): ?>
    <div class="keywords-info">
        <strong>Vurgulanacak Anahtar Kelimeler:</strong>
        <?php
        $keywords = array_map('trim', explode(',', $project['keywords']));
        foreach ($keywords as $kw) {
            if (!empty($kw)) {
                echo '<span class="keyword-tag">' . htmlspecialchars($kw) . '</span>';
            }
        }
        ?>
    </div>
<?php endif; ?>

<?php if (empty($publications)): ?>
    <div class="empty-state">
        <p>Bu projede henüz yayın yok. BibTeX veya RIS dosyası yükleyerek başlayın.</p>
        <button class="btn btn-primary" onclick="showUploadModal()">Dosya Yükle</button>
    </div>
<?php else: ?>
    <div class="viewer-controls">
        <div class="control-group">
            <label>Özet Yazı Boyutu:</label>
            <button onclick="changeFontSize(-1)" class="btn btn-sm">-</button>
            <span id="fontSize">16</span>px
            <button onclick="changeFontSize(1)" class="btn btn-sm">+</button>
        </div>
        <div class="control-group">
            <label>Toplam: <?= count($publications) ?> yayın</label>
            <label>Seçili: <span id="selectedCount"><?= $selectedCount ?></span></label>
        </div>
        <div class="control-group">
            <label>Klavye: ← → Ok tuşları ile gezinin, Space ile seçin</label>
        </div>
    </div>

    <div class="publications-viewer" id="publicationsViewer">
        <?php foreach ($publications as $index => $pub): ?>
            <div class="publication-item <?= $pub['is_selected'] ? 'selected' : '' ?> <?= $index === 0 ? 'active' : '' ?>"
                 data-id="<?= $pub['id'] ?>"
                 data-index="<?= $index ?>">
                <div class="publication-header">
                    <div class="publication-number">#<?= $index + 1 ?></div>
                    <button class="btn-select" onclick="toggleSelect(<?= $pub['id'] ?>, this)">
                        <?= $pub['is_selected'] ? '✓ Seçili' : 'Seç' ?>
                    </button>
                </div>

                <div class="publication-content">
                    <h3 class="publication-title" data-highlight="true">
                        <?= htmlspecialchars($pub['title']) ?>
                    </h3>

                    <?php if (!empty($pub['authors'])): ?>
                        <div class="publication-meta">
                            <strong>Yazarlar:</strong> <?= htmlspecialchars($pub['authors']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="publication-meta-row">
                        <?php if (!empty($pub['year'])): ?>
                            <span><strong>Yıl:</strong> <?= htmlspecialchars($pub['year']) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($pub['type'])): ?>
                            <span><strong>Tür:</strong> <?= htmlspecialchars($pub['type']) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($pub['journal'])): ?>
                            <span><strong>Kaynak:</strong> <?= htmlspecialchars($pub['journal']) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($pub['volume']) || !empty($pub['number']) || !empty($pub['pages'])): ?>
                        <div class="publication-meta">
                            <?php if (!empty($pub['volume'])): ?>
                                Vol. <?= htmlspecialchars($pub['volume']) ?>
                            <?php endif; ?>
                            <?php if (!empty($pub['number'])): ?>
                                (<?= htmlspecialchars($pub['number']) ?>)
                            <?php endif; ?>
                            <?php if (!empty($pub['pages'])): ?>
                                , pp. <?= htmlspecialchars($pub['pages']) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pub['doi'])): ?>
                        <div class="publication-meta">
                            <strong>DOI:</strong>
                            <a href="https://doi.org/<?= htmlspecialchars($pub['doi']) ?>" target="_blank">
                                <?= htmlspecialchars($pub['doi']) ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pub['url'])): ?>
                        <div class="publication-meta">
                            <strong>URL:</strong>
                            <a href="<?= htmlspecialchars($pub['url']) ?>" target="_blank">
                                <?= htmlspecialchars($pub['url']) ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pub['abstract'])): ?>
                        <div class="publication-abstract" data-highlight="true">
                            <strong>Özet:</strong>
                            <p><?= nl2br(htmlspecialchars($pub['abstract'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pub['keywords'])): ?>
                        <div class="publication-meta">
                            <strong>Anahtar Kelimeler:</strong> <?= htmlspecialchars($pub['keywords']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pub['source_file'])): ?>
                        <div class="publication-source">
                            <small>Kaynak: <?= htmlspecialchars($pub['source_file']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Upload Modal -->
<div id="uploadModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>BibTeX/RIS Dosyaları Yükle</h3>
            <button class="modal-close" onclick="hideUploadModal()">&times;</button>
        </div>
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="files">Dosyaları Seçin</label>
                <input type="file" id="files" name="files[]" multiple accept=".bib,.ris"
                       class="form-control" required>
                <small>Birden fazla .bib veya .ris dosyası seçebilirsiniz</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideUploadModal()">İptal</button>
                <button type="submit" class="btn btn-primary">Yükle</button>
            </div>
        </form>
        <div id="uploadProgress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <p>Dosyalar yükleniyor...</p>
        </div>
    </div>
</div>

<script>
const projectId = <?= $project['id'] ?>;
const projectKeywords = <?= json_encode(array_map('trim', explode(',', $project['keywords'] ?? ''))) ?>;
let currentIndex = 0;
let fontSize = 16;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    highlightKeywords();
    updateSelectedCount();
});

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const items = document.querySelectorAll('.publication-item');

    if (items.length === 0) return;

    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        e.preventDefault();
        navigateNext();
    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
        e.preventDefault();
        navigatePrev();
    } else if (e.key === ' ' || e.key === 'Spacebar') {
        e.preventDefault();
        const activeItem = items[currentIndex];
        if (activeItem) {
            const id = activeItem.dataset.id;
            const btn = activeItem.querySelector('.btn-select');
            toggleSelect(id, btn);
        }
    }
});

function navigateNext() {
    const items = document.querySelectorAll('.publication-item');
    if (currentIndex < items.length - 1) {
        items[currentIndex].classList.remove('active');
        currentIndex++;
        items[currentIndex].classList.add('active');
        items[currentIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function navigatePrev() {
    const items = document.querySelectorAll('.publication-item');
    if (currentIndex > 0) {
        items[currentIndex].classList.remove('active');
        currentIndex--;
        items[currentIndex].classList.add('active');
        items[currentIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function toggleSelect(id, btn) {
    fetch('/publication/toggle/' + id, {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = btn.closest('.publication-item');
            item.classList.toggle('selected');

            if (item.classList.contains('selected')) {
                btn.textContent = '✓ Seçili';
            } else {
                btn.textContent = 'Seç';
            }

            updateSelectedCount();
        }
    });
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.publication-item.selected').length;
    const countEl = document.getElementById('selectedCount');
    if (countEl) {
        countEl.textContent = count;
    }
}

function changeFontSize(delta) {
    fontSize += delta;
    if (fontSize < 10) fontSize = 10;
    if (fontSize > 24) fontSize = 24;

    document.getElementById('fontSize').textContent = fontSize;
    document.querySelectorAll('.publication-abstract p').forEach(el => {
        el.style.fontSize = fontSize + 'px';
    });
}

function highlightKeywords() {
    if (!projectKeywords || projectKeywords.length === 0) return;

    const elements = document.querySelectorAll('[data-highlight="true"]');

    elements.forEach(el => {
        let html = el.innerHTML;

        projectKeywords.forEach(keyword => {
            if (keyword.trim() === '') return;

            const regex = new RegExp('(' + escapeRegex(keyword.trim()) + ')', 'gi');
            html = html.replace(regex, '<mark>$1</mark>');
        });

        el.innerHTML = html;
    });
}

function escapeRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function showUploadModal() {
    document.getElementById('uploadModal').style.display = 'flex';
}

function hideUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
    document.getElementById('uploadForm').reset();
    document.getElementById('uploadProgress').style.display = 'none';
}

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    document.getElementById('uploadProgress').style.display = 'block';

    fetch('/project/upload/' + projectId, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${data.imported} yayın başarıyla içe aktarıldı!`);
            location.reload();
        } else {
            alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        alert('Yükleme hatası: ' + error);
    })
    .finally(() => {
        document.getElementById('uploadProgress').style.display = 'none';
    });
});

document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideUploadModal();
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
