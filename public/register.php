<?php
// public/register.php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

require_once __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre' => sanitize($_POST['nombre']),
        'email' => sanitize($_POST['email']),
        'telefono' => sanitize($_POST['telefono']),
        'direccion' => sanitize($_POST['direccion']),
        'contrasena' => sanitize($_POST['contrasena'])
    ];

    require_once __DIR__ . '/../controllers/AuthController.php';
    $auth = new AuthController($pdo);

    if ($auth->registerUser($data)) {
        // Registro exitoso, redirigimos al login
        redirect('login.php');
    } else {
        $error = "No se pudo registrar el usuario. Puede que el email ya esté en uso.";
    }
}
?>

<main class="container my-5">
    <h2>Registrarse</h2>
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Dirección</label>
            <input type="text" name="direccion" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Contraseña</label>
            <input type="password" name="contrasena" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrarse</button>
    </form>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
