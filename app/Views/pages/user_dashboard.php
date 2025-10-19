<?php
/** @var array<int, array> $userNews */
/** @var array<int, array> $latestNews */
/** @var array<int, array> $tvPeruNews */
/** @var bool $canPublish */
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h4 mb-3">Publicar noticia</h2>
                <p class="text-muted small">Comparte novedades de la comunidad o pega un enlace de <strong>TVPerú</strong> para que completemos el contenido automáticamente.</p>
                <form method="post" action="/news">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label for="scrape_url" class="form-label">Enlace de TVPerú (opcional)</label>
                        <div class="input-group">
                            <input type="url" 
                                   class="form-control" 
                                   id="scrape_url" 
                                   name="scrape_url" 
                                   placeholder="https://www.tvperu.gob.pe/noticias/...">
                            <button type="button" 
                                    class="btn btn-outline-primary" 
                                    id="scrapeButton"
                                    onclick="scrapeNews()">
                                🔍 Buscar
                            </button>
                        </div>
                        <div class="form-text">Haz clic en Buscar para autocompletar todos los campos automáticamente.</div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" id="title" name="title" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label for="summary" class="form-label">Resumen</label>
                        <textarea class="form-control" id="summary" name="summary" rows="4" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="author" class="form-label">Autor</label>
                            <input type="text" class="form-control" id="author" name="author" placeholder="Tu nombre o alias">
                        </div>
                        <div class="col-md-6">
                            <label for="source" class="form-label">Fuente</label>
                            <input type="text" class="form-control" id="source" name="source" placeholder="Comunidad, TVPerú...">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="url" class="form-label">Enlace externo</label>
                            <input type="url" class="form-control" id="url" name="url" placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label for="image_url" class="form-label">Imagen destacada</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://...">
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">
                            📤 Publicar noticia
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php if ($tvPeruNews): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h3 class="h5">Últimas notas importadas de TVPerú</h3>
                    <p class="text-muted small">Usa estos enlaces como referencia para mantener tus contenidos alineados con la actualidad nacional.</p>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($tvPeruNews as $note): ?>
                            <li class="list-group-item">
                                <a href="<?= htmlspecialchars($note['url'] ?? '#', ENT_QUOTES) ?>" target="_blank" class="fw-semibold d-block">
                                    <?= htmlspecialchars($note['title'], ENT_QUOTES) ?>
                                </a>
                                <?php if (!empty($note['summary'])): ?>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($note['summary'], ENT_QUOTES) ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Mis publicaciones</h2>
                    <span class="badge bg-primary-subtle text-primary"><?= count($userNews) ?> activas</span>
                </div>
                <?php if ($userNews): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($userNews as $news): ?>
                            <div class="list-group-item py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h3 class="h6 mb-1"><?= htmlspecialchars($news['title'], ENT_QUOTES) ?></h3>
                                        <p class="text-muted small mb-2">Actualizada el <?= htmlspecialchars(date('d/m/Y H:i', strtotime($news['updated_at'] ?? $news['created_at'] ?? 'now')), ENT_QUOTES) ?></p>
                                        <p class="mb-0"><?= htmlspecialchars(mb_strimwidth($news['summary'] ?? '', 0, 150, '…'), ENT_QUOTES) ?></p>
                                    </div>
                                    <div class="ms-3 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="modal" data-bs-target="#editNewsModal<?= (int) $news['id'] ?>">
                                            ✏️ Editar
                                        </button>
                                        <form method="post" action="/news/<?= (int) $news['id'] ?>/delete" onsubmit="return confirm('¿Deseas eliminar esta noticia?');">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                🗑️ Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="editNewsModal<?= (int) $news['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <form method="post" action="/news/<?= (int) $news['id'] ?>/update">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar noticia</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label" for="scrape_url_<?= (int) $news['id'] ?>">Actualizar desde TVPerú (opcional)</label>
                                                    <input type="url" class="form-control" id="scrape_url_<?= (int) $news['id'] ?>" name="scrape_url" placeholder="https://www.tvperu.gob.pe/noticias/...">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="title_<?= (int) $news['id'] ?>">Título</label>
                                                    <input type="text" class="form-control" id="title_<?= (int) $news['id'] ?>" name="title" value="<?= htmlspecialchars($news['title'], ENT_QUOTES) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="summary_<?= (int) $news['id'] ?>">Resumen</label>
                                                    <textarea class="form-control" id="summary_<?= (int) $news['id'] ?>" name="summary" rows="4" required><?= htmlspecialchars($news['summary'] ?? '', ENT_QUOTES) ?></textarea>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="author_<?= (int) $news['id'] ?>">Autor</label>
                                                        <input type="text" class="form-control" id="author_<?= (int) $news['id'] ?>" name="author" value="<?= htmlspecialchars($news['author'] ?? '', ENT_QUOTES) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="source_<?= (int) $news['id'] ?>">Fuente</label>
                                                        <input type="text" class="form-control" id="source_<?= (int) $news['id'] ?>" name="source" value="<?= htmlspecialchars($news['source'] ?? '', ENT_QUOTES) ?>">
                                                    </div>
                                                </div>
                                                <div class="row g-3 mt-1">
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="url_<?= (int) $news['id'] ?>">Enlace externo</label>
                                                        <input type="url" class="form-control" id="url_<?= (int) $news['id'] ?>" name="url" value="<?= htmlspecialchars($news['url'] ?? '', ENT_QUOTES) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="image_url_<?= (int) $news['id'] ?>">Imagen destacada</label>
                                                        <input type="url" class="form-control" id="image_url_<?= (int) $news['id'] ?>" name="image_url" value="<?= htmlspecialchars($news['image_url'] ?? '', ENT_QUOTES) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <p class="text-muted">Aún no has publicado noticias. ¡Comparte la primera usando el formulario de la izquierda!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section class="mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Actividad reciente de la comunidad</h2>
        <span class="text-muted small">Contenido actualizado continuamente</span>
    </div>
    <div class="row g-4">
        <?php foreach ($latestNews as $news): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <?php if (!empty($news['image_url'])): ?>
                        <img src="<?= htmlspecialchars($news['image_url'], ENT_QUOTES) ?>" class="card-img-top" alt="<?= htmlspecialchars($news['title'], ENT_QUOTES) ?>">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-light text-primary mb-2">🌐 <?= htmlspecialchars($news['source'] ?? 'General', ENT_QUOTES) ?></span>
                        <h3 class="h5"><?= htmlspecialchars($news['title'], ENT_QUOTES) ?></h3>
                        <?php if (!empty($news['summary'])): ?>
                            <p class="text-muted flex-grow-1"><?= htmlspecialchars(mb_strimwidth($news['summary'], 0, 160, '…'), ENT_QUOTES) ?></p>
                        <?php endif; ?>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <small class="text-muted">👤 <?= htmlspecialchars($news['author'] ?? ($news['owner_username'] ?? 'Anónimo'), ENT_QUOTES) ?></small>
                            <?php if (!empty($news['url'])): ?>
                                <a href="<?= htmlspecialchars($news['url'], ENT_QUOTES) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Ver más</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$latestNews): ?>
            <div class="col-12">
                <div class="alert alert-info mb-0">Todavía no hay actividad reciente.</div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function scrapeNews() {
    const urlInput = document.getElementById('scrape_url');
    const scrapeButton = document.getElementById('scrapeButton');
    const url = urlInput.value.trim();
    
    if (!url) {
        alert('Por favor ingresa una URL de TVPerú');
        return;
    }
    
    if (!url.includes('tvperu.gob.pe') && !url.includes('noticias.tpe.pe')) {
        alert('Solo se permiten enlaces de TVPerú (tvperu.gob.pe o noticias.tpe.pe)');
        return;
    }
    
    scrapeButton.innerHTML = '⏳ Buscando...';
    scrapeButton.disabled = true;
    
    fetch('/news/scrape', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({'url': url, 'csrf_token': '<?= csrf_token() ?>'})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('title').value = data.news.title || '';
            document.getElementById('summary').value = data.news.summary || '';
            document.getElementById('author').value = data.news.author || 'TVPerú Noticias';
            document.getElementById('source').value = data.news.source || 'TVPerú';
            document.getElementById('image_url').value = data.news.image_url || '';
            document.getElementById('url').value = data.news.url || url;
            document.getElementById('title').focus();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(() => alert('Error al conectar con el servidor'))
    .finally(() => {
        scrapeButton.innerHTML = '🔍 Buscar';
        scrapeButton.disabled = false;
    });
}

document.getElementById('scrape_url').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        scrapeNews();
    }
});
</script>