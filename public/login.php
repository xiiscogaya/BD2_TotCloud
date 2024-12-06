<?php
// public/login.php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

require_once __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['contrasena']);

    require_once __DIR__ . '/../controllers/AuthController.php';
    $auth = new AuthController($pdo);

    if ($auth->loginUser($email, $password)) {
        redirect('index.php');
    } else {
        $error = "Email o contraseña inválidos.";
    }
}
?>

<main class="container my-5">
    <h2>Iniciar Sesión</h2>
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Contraseña</label>
            <input type="password" name="contrasena" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
    </form>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
