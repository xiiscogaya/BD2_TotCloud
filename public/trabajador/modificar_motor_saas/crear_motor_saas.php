<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Datos principales de Motor
    $nombre = trim($_POST['nombre']);
    $version = trim($_POST['version']);
    $precioH = floatval($_POST['precioH']);

    if (empty($nombre) || empty($version) || $precioH <= 0) {
        $message = 'Todos los campos son obligatorios.';
    } else {
        // Determinar el siguiente ID disponible para Motor
        $query_next_id = "SELECT COALESCE(MIN(a.idMotor)+1, 0) AS next_id FROM motor a LEFT JOIN motor b ON a.idMotor = b.idMotor-1 WHERE b.idMotor IS NULL";
        $result_next_id = $conn->query($query_next_id);
        $row_next_id = $result_next_id->fetch_assoc();
        $idMotor = $row_next_id['next_id'];

        // Crear Motor
        $query_motor = "INSERT INTO motor (idMotor, Nombre, Version, PrecioH) VALUES (?, ?, ?, ?)";
        $stmt_motor = $conn->prepare($query_motor);
        $stmt_motor->bind_param('issi', $idMotor, $nombre, $version, $precioH);    }
        if ($stmt_motor->execute()) {

            //Guardar mensaje en sesión
            $_SESSION['success_message_añadir'] = 'Motor añadido correctamente.';

            // Redirigir a modificar_motor_saas.php
            header('Location: modificar_motor_saas.php');
        } else {
            $message = 'Ha ocurrido un error al crear el Motor.';
        }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Motor - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Crear Motor</h1>
    </header>

    <main class="container my-5">
        <!-- Botón de Volver -->
        <div class="container my-3">
            <a href="modificar_motor_saas.php" class="btn btn-secondary">Volver</a>
        </div>
        <h2 class="text-center">Nuevo Motor</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info text-center"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="crear_motor_saas.php" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>

            <div class="mb-3">
                <label for="version" class="form-label">Versión</label>
                <input type="text" class="form-control" id="version" name="version" required>
            </div>

            <div class="mb-3">
                <label for="precioH" class="form-label">Precio por Hora</label>
                <input type="number" class="form-control" id="precioH" name="precioH" step="0.01" min="0" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Crear Motor</button>
        </form>
    </main>
</body>

</html>