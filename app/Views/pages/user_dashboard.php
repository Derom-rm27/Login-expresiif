<?php
/** @var array<int, array> $newsItems */
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Centro de noticias</h1>
    <form method="post" action="/scrape-news" class="mb-0">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-sync-alt me-1"></i>Actualizar noticias
        </button>
    </form>
</div>
<div class="row g-4">
    <?php foreach ($newsItems as $news): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-secondary mb-2"><?= htmlspecialchars($news['source'] ?? 'General', ENT_QUOTES) ?></span>
                    <h5 class="card-title"><?= htmlspecialchars($news['title'], ENT_QUOTES) ?></h5>
                    <?php if (!empty($news['summary'])): ?>
                        <p class="card-text flex-grow-1"><?= htmlspecialchars($news['summary'], ENT_QUOTES) ?></p>
                    <?php endif; ?>
                    <div class="mt-auto">
                        <p class="text-muted small mb-2">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($news['author'] ?? 'Anónimo', ENT_QUOTES) ?>
                        </p>
                        <?php if (!empty($news['url'])): ?>
                            <a href="<?= htmlspecialchars($news['url'], ENT_QUOTES) ?>" class="btn btn-outline-primary btn-sm" target="_blank">Leer más</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$newsItems): ?>
        <div class="col-12">
            <div class="alert alert-info">No hay noticias disponibles. Usa el botón para generar contenido.</div>
        </div>
    <?php endif; ?>
</div>