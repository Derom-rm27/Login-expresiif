<?php
/** @var int $totalVisits */
/** @var int $todayVisits */
/** @var int $uniqueVisitors */
/** @var array $statsByBrowser */
/** @var array $statsByOS */
/** @var array $visitsByIP */
/** @var array $visitsByHour */
/** @var array $topPages */
/** @var bool $hasDetailedData */
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">📊 Reporte de Visitas</h1>
        <a href="/admin-dashboard" class="btn btn-outline-secondary">← Volver al Panel</a>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total de Visitas</h5>
                            <h2 class="display-4"><?= number_format($totalVisits) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Visitas Hoy</h5>
                            <h2 class="display-4"><?= number_format($todayVisits) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Visitantes Únicos</h5>
                            <h2 class="display-4"><?= number_format($uniqueVisitors) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($totalVisits === 0): ?>
        <div class="alert alert-warning text-center">
            <h4>📈 No hay datos de visitas aún</h4>
            <p class="mb-0">Las estadísticas se mostrarán aquí una vez que los usuarios visiten tu sitio web.</p>
        </div>
    <?php else: ?>

        <div class="row">
            <!-- Estadísticas por Navegador -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">🌐 Navegadores Más Usados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Navegador</th>
                                        <th>Visitas</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($statsByBrowser as $browser): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($browser['browser'] ?: 'Desconocido') ?></td>
                                            <td><?= number_format($browser['count']) ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= number_format($browser['percentage'], 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas por Sistema Operativo -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">💻 Sistemas Operativos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Sistema Operativo</th>
                                        <th>Visitas</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($statsByOS as $os): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($os['operating_system'] ?: 'Desconocido') ?></td>
                                            <td><?= number_format($os['count']) ?></td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?= number_format($os['percentage'], 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top IPs -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">🔍 Top IPs por Visitas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Visitas</th>
                                        <th>Última Visita</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($visitsByIP as $visit): ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($visit['ip_address']) ?></code></td>
                                            <td><span class="badge bg-primary"><?= (int) $visit['visit_count'] ?></span></td>
                                            <td><small><?= htmlspecialchars(date('d/m/Y H:i', strtotime($visit['last_visit']))) ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($visitsByIP)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No hay datos de IPs</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Páginas -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">📄 Páginas Más Visitadas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Página</th>
                                        <th>Visitas</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topPages as $visit): ?>
                                        <tr>
                                            <td>
                                                <code><?= htmlspecialchars($visit['page'] === '' ? '/' : $visit['page']) ?></code>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= number_format($visit['visits']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= $totalVisits > 0 ? number_format(($visit['visits'] / $totalVisits) * 100, 1) : 0 ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($topPages)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No hay datos de páginas</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Visitas por Hora -->
        <?php if (!empty($visitsByHour)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">🕒 Visitas por Hora del Día</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Visitas</th>
                                        <th>Barra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $maxVisits = max(array_column($visitsByHour, 'visits'));
                                    foreach ($visitsByHour as $hour): 
                                    ?>
                                        <tr>
                                            <td><?= sprintf('%02d:00', $hour['hour']) ?></td>
                                            <td><?= number_format($hour['visits']) ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" 
                                                         role="progressbar" 
                                                         style="width: <?= $maxVisits > 0 ? ($hour['visits'] / $maxVisits) * 100 : 0 ?>%"
                                                         aria-valuenow="<?= $hour['visits'] ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="<?= $maxVisits ?>">
                                                        <?= $hour['visits'] ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (!$hasDetailedData && $totalVisits > 0): ?>
    <div class="alert alert-info mt-4">
        <h6>💡 Para obtener estadísticas detalladas:</h6>
        <p class="mb-2">Ejecuta este SQL en tu base de datos para habilitar el tracking completo:</p>
        <pre class="bg-dark text-light p-3 rounded small">CREATE TABLE IF NOT EXISTS detailed_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    browser VARCHAR(100),
    operating_system VARCHAR(100),
    page_url VARCHAR(500) DEFAULT '/',
    visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</pre>
    </div>
    <?php endif; ?>
</div>