<?php
/** @var array<int, array> $banners */
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Crear banner</h2>
                <form method="post" action="/upload-banner" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="link" class="form-label">Enlace (opcional)</label>
                        <input type="url" class="form-control" id="link" name="link">
                    </div>
                    <div class="mb-3">
                        <label for="banner" class="form-label">Imagen</label>
                        <input type="file" class="form-control" id="banner" name="banner" accept="image/*">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Activar inmediatamente</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar banner</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Banners existentes</h2>
                <?php if ($banners): ?>
                    <div class="list-group">
                        <?php foreach ($banners as $banner): ?>
                            <div class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($banner['title'], ENT_QUOTES) ?></h5>
                                    <?php if (!empty($banner['description'])): ?>
                                        <p class="mb-1 text-muted"><?= htmlspecialchars($banner['description'], ENT_QUOTES) ?></p>
                                    <?php endif; ?>
                                    <span class="badge <?= $banner['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $banner['is_active'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-secondary" href="/toggle-banner/<?= (int) $banner['id'] ?>">
                                        <?= $banner['is_active'] ? 'Desactivar' : 'Activar' ?>
                                    </a>
                                    <a class="btn btn-sm btn-outline-danger" href="/delete-banner/<?= (int) $banner['id'] ?>" onclick="return confirm('¿Eliminar banner?');">Eliminar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Aún no se han creado banners.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>