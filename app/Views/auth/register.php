<?php
/** @var string|null $error */
/** @var array $formData */
?>
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h3 mb-3 text-center">Crear cuenta</h1>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
                <?php endif; ?>
                <form method="post" action="/register">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <p class="small text-muted">La contraseña debe tener al menos 10 caracteres, incluir letras en mayúscula y minúscula, números y símbolos.</p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="username" class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($formData['username'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '', ENT_QUOTES) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mt-4">Registrarse</button>
                </form>
            </div>
        </div>
    </div>
</div>