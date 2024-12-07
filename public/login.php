<?php
session_start();
include_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validación de entrada básica
    if (empty($username) || empty($password)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        $query = "SELECT u.idUsuario, u.Usuario, u.Contraseña, g.idGrupo, g.Nombre AS grupo_nombre, 
                         o.idOrganizacion, o.Nombre AS organizacion_nombre
                  FROM usuario u
                  INNER JOIN r_usuario_org ruo ON u.idUsuario = ruo.idUsuario
                  INNER JOIN organizacion o ON ruo.idOrganizacion = o.idOrganizacion
                  INNER JOIN r_org_grup rog ON o.idOrganizacion = rog.idOrganizacion
                  INNER JOIN grupo g ON rog.idGrupo = g.idGrupo
                  WHERE u.Usuario = ?";

        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['Contraseña'])) {
                    // Configurar variables de sesión
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['idUsuario'];
                    $_SESSION['username'] = $user['Usuario'];
                    $_SESSION['grupo_id'] = $user['idGrupo'];
                    $_SESSION['grupo_nombre'] = $user['grupo_nombre'];
                    $_SESSION['organizacion_id'] = $user['idOrganizacion'];
                    $_SESSION['organizacion_nombre'] = $user['organizacion_nombre'];

                    // Redirigir según el grupo del usuario
                    if ($user['grupo_nombre'] === 'Administradores') {
                        header("Location: admin_dashboard.php"); // Redirige a la página especial para administradores
                    } else {
                        header("Location: select_paas.php"); // Redirige al flujo normal
                    }
                    exit;
                } else {
                    $error = "Usuario o contraseña incorrectos.";
                }
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        } else {
            die("Error al preparar la consulta: " . $conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TotCloud</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container my-5">
        <h2 class="text-center">Iniciar Sesión</h2>

        <form method="POST" class="mx-auto" style="max-width: 400px;">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

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

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
