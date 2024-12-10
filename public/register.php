<?php
session_start();
include '../includes/db_connect.php'; // Conexión a la base de datos

$error = ''; // Inicializar el mensaje de error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y sanitizar datos del formulario
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim($_POST['address']);

    // Validar que los campos requeridos estén completos
    if (empty($name) || empty($username) || empty($email) || empty($phone) || empty($password) || empty($confirm_password) || empty($address)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (!preg_match('/^\d{7,15}$/', $phone)) {
        $error = 'El número de teléfono debe contener entre 7 y 15 dígitos.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        // Verificar si el usuario o correo ya existen
        $query = "SELECT * FROM usuario WHERE Usuario = ? OR Email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'El nombre de usuario o el correo electrónico ya están registrados.';
        } else {
            // Obtener el próximo ID disponible para el usuario
            $query_next_id = "SELECT COALESCE(MIN(a.idUsuario)+1, 0) AS next_id FROM usuario a LEFT JOIN usuario b ON a.idUsuario = b.idUsuario-1 WHERE b.idUsuario IS NULL";
            $result_next_id = $conn->query($query_next_id);
            $next_id = $result_next_id->fetch_assoc()['next_id'];

            // Hash de la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insertar el nuevo usuario en la base de datos
            $insert_query = "INSERT INTO usuario (idUsuario, Nombre, Usuario, Email, Telefono, Contraseña, Direccion) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param('issssss', $next_id, $name, $username, $email, $phone, $hashed_password, $address);

            if ($stmt->execute()) {
                // Redirigir al inicio de sesión después del registro exitoso
                header('Location: login.php');
                exit;
            } else {
                $error = 'Ocurrió un error al registrar el usuario. Intenta de nuevo.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <?php include '../includes/header.php'; ?>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center">Registrarse</h2>

        <!-- Mostrar mensaje de error -->
        <?php if ($error): ?>
        <div class="alert alert-danger text-center">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Formulario de registro -->
        <form method="POST" action="register.php" class="mx-auto" style="max-width: 400px;">
            <div class="mb-3">
                <label for="name" class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Registrarse</button>
        </form>
    </main>

    <!-- Pie de página -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
