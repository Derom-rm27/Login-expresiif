<?php
/** @var array<int, array> $activeBanners */
/** @var array<int, array> $activeNews */
/** @var array|null $currentUser */
?>
<div class="row mb-4">
    <div class="col-12">
        <?php if ($activeBanners): ?>
            <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($activeBanners as $index => $banner): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <?php if (!empty($banner['image_path'])): ?>
                                <img src="/uploads/<?= htmlspecialchars($banner['image_path'], ENT_QUOTES) ?>" class="d-block w-100 banner-image" alt="<?= htmlspecialchars($banner['title'], ENT_QUOTES) ?>">
                            <?php else: ?>
                                <div class="banner-placeholder d-flex align-items-center justify-content-center">
                                    <h3 class="text-white"><?= htmlspecialchars($banner['title'], ENT_QUOTES) ?></h3>
                                </div>
                            <?php endif; ?>
                            <div class="carousel-caption d-none d-md-block">
                                <h5><?= htmlspecialchars($banner['title'], ENT_QUOTES) ?></h5>
                                <?php if (!empty($banner['description'])): ?>
                                    <p><?= htmlspecialchars($banner['description'], ENT_QUOTES) ?></p>
                                <?php endif; ?>
                                <?php if (!$currentUser): ?>
                                    <a href="/register" class="btn btn-primary">Únete a la comunidad</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($activeBanners) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="banner-placeholder d-flex align-items-center justify-content-center">
                <div class="text-center text-white">
                    <h3>Bienvenido <?= $currentUser ? htmlspecialchars($currentUser['username'], ENT_QUOTES) : 'invitado' ?></h3>
                    <p>No hay banners activos en este momento.</p>
                    <a href="<?= $currentUser ? '/user-dashboard' : '/register' ?>" class="btn btn-primary mt-3">
                        <?= $currentUser ? 'Explorar noticias' : 'Crear cuenta' ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<section class="py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Noticias recientes</h2>
        <?php if ($currentUser): ?>
            <a href="/user-dashboard" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-newspaper me-2"></i>Ver todas
            </a>
        <?php endif; ?>
    </div>
    <div class="row g-4">
        <?php foreach ($activeNews as $news): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($news['title'], ENT_QUOTES) ?></h5>
                        <?php if (!empty($news['summary'])): ?>
                            <p class="card-text flex-grow-1"><?= htmlspecialchars($news['summary'], ENT_QUOTES) ?></p>
                        <?php endif; ?>
                        <div class="mt-auto">
                            <p class="text-muted small mb-2">
                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($news['author'] ?? 'Anónimo', ENT_QUOTES) ?>
                                <span class="ms-3"><i class="fas fa-globe me-1"></i><?= htmlspecialchars($news['source'] ?? 'General', ENT_QUOTES) ?></span>
                            </p>
                            <?php if (!empty($news['url'])): ?>
                                <a href="<?= htmlspecialchars($news['url'], ENT_QUOTES) ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100">Leer más</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$activeNews): ?>
            <div class="col-12">
                <div class="alert alert-info">Aún no hay noticias disponibles.</div>
            </div>
        <?php endif; ?>
    </div>
</section>