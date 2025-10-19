<?php
/** @var array<int, array> $activeBanners */
/** @var array<int, array> $activeNews */
/** @var array<int, array> $latestNews */
/** @var array<int, array> $tvPeruHighlights */
/** @var array<int, array{source:string,total:int}> $sourceStats */
/** @var array<string, int> $metrics */
/** @var array<int, array{page:string, visits:int}> $topPages */
/** @var array|null $currentUser */
?>
<section class="hero-section position-relative overflow-hidden rounded-4 mb-5">
    <div class="row align-items-center">
        <div class="col-lg-6 py-5 px-4 px-lg-5">
            <span class="badge bg-gradient text-uppercase mb-3">Calidad y Comunidad</span>
            <h1 class="display-5 fw-bold text-white mb-3">Noticias verificadas y aportes ciudadanos en un solo lugar.</h1>
            <p class="lead text-white-50">Consulta lo último del ecosistema tecnológico, importa artículos de TVPerú y comparte tus propios reportes con la comunidad.</p>
            <div class="d-flex gap-3 mt-4">
                <a href="<?= $currentUser ? '/user-dashboard' : '/register' ?>" class="btn btn-light btn-lg shadow-sm">
                    <i class="fas fa-bolt me-2"></i><?= $currentUser ? 'Gestionar mis noticias' : 'Crear cuenta gratuita' ?>
                </a>
                <a href="#news-feed" class="btn btn-outline-light btn-lg"><i class="fas fa-arrow-down me-2"></i>Explorar novedades</a>
            </div>
            <div class="row g-3 mt-5 text-white">
                <div class="col-6 col-sm-4">
                    <div class="metric-card">
                        <span class="metric-value"><?= number_format($metrics['news'] ?? 0) ?></span>
                        <span class="metric-label">Noticias publicadas</span>
                    </div>
                </div>
                <div class="col-6 col-sm-4">
                    <div class="metric-card">
                        <span class="metric-value"><?= number_format($metrics['tvperu'] ?? 0) ?></span>
                        <span class="metric-label">Notas de TVPerú</span>
                    </div>
                </div>
                <div class="col-6 col-sm-4">
                    <div class="metric-card">
                        <span class="metric-value"><?= number_format($metrics['users'] ?? 0) ?></span>
                        <span class="metric-label">Usuarios activos</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 p-4">
            <?php if ($activeBanners): ?>
                <div id="bannerCarousel" class="carousel slide shadow-lg" data-bs-ride="carousel">
                    <div class="carousel-inner rounded-4">
                        <?php foreach ($activeBanners as $index => $banner): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <?php if (!empty($banner['image_path'])): ?>
                                    <img src="/uploads/<?= htmlspecialchars($banner['image_path'], ENT_QUOTES) ?>" class="d-block w-100 banner-image" alt="<?= htmlspecialchars($banner['title'], ENT_QUOTES) ?>">
                                <?php else: ?>
                                    <div class="banner-placeholder d-flex align-items-center justify-content-center text-center px-4">
                                        <h3 class="text-white mb-2"><?= htmlspecialchars($banner['title'], ENT_QUOTES) ?></h3>
                                        <?php if (!empty($banner['description'])): ?>
                                            <p class="text-white-50 mb-0"><?= htmlspecialchars($banner['description'], ENT_QUOTES) ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="carousel-caption d-none d-md-block">
                                    <h5><?= htmlspecialchars($banner['title'], ENT_QUOTES) ?></h5>
                                    <?php if (!empty($banner['description'])): ?>
                                        <p><?= htmlspecialchars($banner['description'], ENT_QUOTES) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($banner['link'])): ?>
                                        <a href="<?= htmlspecialchars($banner['link'], ENT_QUOTES) ?>" class="btn btn-primary btn-sm" target="_blank">Saber más</a>
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
                <div class="empty-banner text-center text-white-50">
                    <i class="fas fa-image fa-3x mb-3"></i>
                    <p class="mb-0">Aún no hay banners destacados. Los moderadores pueden crearlos desde su panel.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="news-feed" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Explora las últimas noticias</h2>
        <span class="text-muted small">Actualizado cada vez que la comunidad comparte</span>
    </div>
    <div class="row g-4">
        <?php foreach ($latestNews as $news): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 news-card shadow-sm">
                    <?php if (!empty($news['image_url'])): ?>
                        <img src="<?= htmlspecialchars($news['image_url'], ENT_QUOTES) ?>" class="card-img-top" alt="<?= htmlspecialchars($news['title'], ENT_QUOTES) ?>">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge rounded-pill bg-primary-subtle text-primary"><i class="fas fa-tag me-1"></i><?= htmlspecialchars($news['source'] ?? 'General', ENT_QUOTES) ?></span>
                            <small class="text-muted"><i class="fas fa-clock me-1"></i><?= htmlspecialchars(date('d M', strtotime($news['created_at'] ?? 'now')), ENT_QUOTES) ?></small>
                        </div>
                        <h3 class="h5 mb-2"><?= htmlspecialchars($news['title'], ENT_QUOTES) ?></h3>
                        <?php if (!empty($news['summary'])): ?>
                            <p class="text-muted flex-grow-1 mb-3"><?= htmlspecialchars(mb_strimwidth($news['summary'], 0, 150, '…'), ENT_QUOTES) ?></p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <small class="text-muted"><i class="fas fa-user me-1"></i><?= htmlspecialchars($news['author'] ?? ($news['owner_username'] ?? 'Anónimo'), ENT_QUOTES) ?></small>
                            <?php if (!empty($news['url'])): ?>
                                <a href="<?= htmlspecialchars($news['url'], ENT_QUOTES) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Leer más</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$latestNews): ?>
            <div class="col-12">
                <div class="alert alert-info">Aún no hay noticias publicadas.</div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($tvPeruHighlights): ?>
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Destacados desde TVPerú</h2>
        <span class="text-muted small">Importa cualquiera con un clic desde tu panel</span>
    </div>
    <div class="row g-4">
        <?php foreach ($tvPeruHighlights as $highlight): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm tvperu-card">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-danger-subtle text-danger mb-2"><i class="fas fa-broadcast-tower me-1"></i>TVPerú</span>
                        <h3 class="h5"><?= htmlspecialchars($highlight['title'], ENT_QUOTES) ?></h3>
                        <?php if (!empty($highlight['summary'])): ?>
                            <p class="text-muted flex-grow-1"><?= htmlspecialchars(mb_strimwidth($highlight['summary'], 0, 140, '…'), ENT_QUOTES) ?></p>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars($highlight['url'] ?? '#', ENT_QUOTES) ?>" target="_blank" class="btn btn-sm btn-outline-danger mt-auto">Ver en TVPerú</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="mb-5">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h5 mb-3">Fuentes más activas</h3>
                    <?php if ($sourceStats): ?>
                        <?php foreach ($sourceStats as $stat): ?>
                            <?php $percentage = ($metrics['news'] ?? 0) > 0 ? (int) round(($stat['total'] / max($metrics['news'], 1)) * 100) : 0; ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold"><?= htmlspecialchars($stat['source'], ENT_QUOTES) ?></span>
                                    <span class="text-muted small"><?= $stat['total'] ?> publicaciones</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Aún no hay suficientes datos de fuentes.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h5 mb-3">Secciones más visitadas</h3>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($topPages as $page): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-link me-2 text-primary"></i><?= htmlspecialchars($page['page'], ENT_QUOTES) ?></span>
                                <span class="badge bg-primary-subtle text-primary"><?= number_format($page['visits']) ?> visitas</span>
                            </li>
                        <?php endforeach; ?>
                        <?php if (!$topPages): ?>
                            <li class="list-group-item text-muted">Aún no se registran visitas.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>