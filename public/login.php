<?php
session_start();
include '../includes/db_connect.php'; // Conexión a la base de datos

$error = ''; // Mensaje de error inicial

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Consulta para buscar el usuario por nombre de usuario
    $query = "SELECT * FROM usuario WHERE Usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar la contraseña con password_verify
        if (password_verify($password, $user['Contraseña'])) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['idUsuario'];
            $_SESSION['user_name'] = $user['Nombre'];
            $_SESSION['username'] = $user['Usuario'];

            // Verificar si el usuario es trabajador
            $worker_query = "SELECT * FROM trabajador WHERE idUsuario = ?";
            $stmt_worker = $conn->prepare($worker_query);
            $stmt_worker->bind_param('i', $user['idUsuario']);
            $stmt_worker->execute();
            $worker_result = $stmt_worker->get_result();

            if ($worker_result->num_rows === 1) {
                // Si es trabajador, redirigir al dashboard de trabajador
                header('Location: trabajador/trabajador.php');
                exit;
            } else {
                // Si es usuario normal, redirigir al dashboard de usuario
                header('Location: usuario_dashboard.php');
                exit;
            }
        } else {
            $error = 'La contraseña es incorrecta.';
        }
    } else {
        $error = 'El usuario no existe.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <?php include '../includes/header.php'; ?>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center">Iniciar Sesión</h2>

        <!-- Mostrar error si existe -->
        <?php if ($error): ?>
        <div class="alert alert-danger text-center">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Formulario de inicio de sesión -->
        <form method="POST" action="login.php" class="mx-auto" style="max-width: 400px;">
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
        </form>
    </main>

    <!-- Pie de página -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
