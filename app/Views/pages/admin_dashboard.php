<?php
/** @var array<int, array> $users */
?>
<h1 class="h4 mb-4">Panel de administración</h1>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Roles</th>
                    <th>Verificación</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($user['email'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars(format_roles($user['roles']), ENT_QUOTES) ?></td>
                        <td>
                            <?php if (!empty($user['email_verified_at'])): ?>
                                <span class="badge bg-success">Verificado</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" action="/manage-users/update-roles/<?= (int) $user['id'] ?>" class="d-flex flex-wrap gap-2">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <?php foreach (['ROLE_USER' => 'Usuario', 'ROLE_MODERATOR' => 'Moderador', 'ROLE_ADMIN' => 'Admin'] as $value => $label): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="role_<?= $value ?>_<?= (int) $user['id'] ?>" name="roles[]" value="<?= $value ?>" <?= in_array($value, $user['roles'], true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="role_<?= $value ?>_<?= (int) $user['id'] ?>"><?= $label ?></label>
                                    </div>
                                <?php endforeach; ?>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>