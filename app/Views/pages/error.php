<?php
/** @var string $message */
?>
<div class="text-center py-5">
    <h1 class="display-5 mb-3">¡Ups!</h1>
    <p class="lead mb-4"><?= htmlspecialchars($message ?? 'Algo salió mal.', ENT_QUOTES) ?></p>
    <a href="/" class="btn btn-primary">Volver al inicio</a>
</div>