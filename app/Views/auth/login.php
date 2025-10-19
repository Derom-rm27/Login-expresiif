<?php
/** @var string|null $error */
/** @var array<string, mixed>|null $captcha */
/** @var string|null $resendEmail */
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h3 mb-3 text-center">Iniciar sesión</h1>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
                <?php endif; ?>
                <form method="post" action="/login">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Verificación</label>
                        <?php if (($captcha['mode'] ?? null) === 'turnstile'): ?>
                            <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($captcha['site_key'] ?? '', ENT_QUOTES) ?>" data-theme="light"></div>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= htmlspecialchars($captcha['image_url'] ?? '/captcha/image', ENT_QUOTES) ?>" alt="Captcha" width="160" height="54" class="rounded border">
                                <input type="text" class="form-control" id="captcha" name="captcha" required placeholder="<?= htmlspecialchars($captcha['prompt'] ?? 'Ingresa los números', ENT_QUOTES) ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
                <?php if (!empty($resendEmail)): ?>
                    <hr>
                    <form method="post" action="/email/resend-verification" class="text-center">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($resendEmail, ENT_QUOTES) ?>">
                        <p class="mb-2">¿No recibiste el correo de confirmación?</p>
                        <button type="submit" class="btn btn-link">Enviar nuevamente el enlace</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>