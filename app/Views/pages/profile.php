<?php
/** @var array $userProfile */
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h4 mb-3">Información de la cuenta</h2>
                <ul class="list-unstyled mb-0">
                    <li><strong>Usuario:</strong> <?= htmlspecialchars($userProfile['username'], ENT_QUOTES) ?></li>
                    <li><strong>Correo:</strong> <?= htmlspecialchars($userProfile['email'], ENT_QUOTES) ?></li>
                    <li><strong>Roles:</strong> <?= htmlspecialchars(format_roles($userProfile['roles']), ENT_QUOTES) ?></li>
                    <li>
                        <strong>Estado del correo:</strong>
                        <?php if (!empty($userProfile['email_verified_at'])): ?>
                            <span class="badge bg-success">Verificado</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Pendiente de verificación</span>
                        <?php endif; ?>
                    </li>
                </ul>
                <?php if (empty($userProfile['email_verified_at'])): ?>
                    <form method="post" action="/email/resend-verification" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($userProfile['email'], ENT_QUOTES) ?>">
                        <button type="submit" class="btn btn-outline-warning btn-sm">Reenviar correo de verificación</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h3 class="h5 mb-3">Actualizar nombre de usuario</h3>
                <form method="post" action="/profile/update-username">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de usuario</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($userProfile['username'], ENT_QUOTES) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </form>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="h5 mb-3">Actualizar contraseña</h3>
                <?php if (empty($userProfile['email_verified_at'])): ?>
                    <div class="alert alert-warning">Debes verificar tu correo antes de poder confirmar un cambio de contraseña.</div>
                <?php endif; ?>
                <p class="small text-muted">La contraseña debe tener mínimo 10 caracteres, incluir letras en mayúscula y minúscula, números y símbolos.</p>
                <form method="post" action="/profile/update-password">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">Actualizar contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>