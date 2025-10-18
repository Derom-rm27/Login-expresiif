<?php
/** @var array<int, array{page:string, visits:int}> $visits */
?>
<h1 class="h4 mb-4">Reporte de visitas</h1>
<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($visits): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Página</th>
                        <th>Visitas</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($visits as $visit): ?>
                        <tr>
                            <td><?= htmlspecialchars($visit['page'], ENT_QUOTES) ?></td>
                            <td><?= (int) $visit['visits'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">Aún no se registran visitas.</p>
        <?php endif; ?>
    </div>
</div>