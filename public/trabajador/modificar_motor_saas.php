<?php
session_start();
include '../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
// Mostrar mensaje de éxito si existe
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Eliminar mensaje para evitar que se muestre de nuevo
}

// Obtener todas las configuraciones Motor
$query = "SELECT * FROM motor";
$result = $conn->query($query);

// Manejar cambios de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idMotor'], $_POST['estado'])) {
    $idMotor = intval($_POST['idMotor']);
    $estado = $_POST['estado'] === 'Activo' ? 'Activo' : 'En pruebas';

    $update_query = "UPDATE motor SET Estado = ? WHERE idMotor = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('si', $estado, $idMotor);
    $stmt->execute();

    // Recargar la página para reflejar los cambios
    header("Location: modificar_motor.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Lista Motor - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../css/estilos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .slider-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slider-lable {
            margin: 0 10px;
        }
    </style>
</head>

<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white text-center py-3">
        <h1>Modificar Lista De Motores</h1>
    </header>

    <!-- Botones de volver a Trabajador -->
    <div class="container my-3">
        <a href="trabajador.php" class="btn btn-secondary">Volver</a>
    </div>

    <!-- Mostrar mensaje de cambios realizdos -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success text-center">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Contenido principal -->
    <h2 class="mb-4">Catalogo de motores de bases de datos</h2>
    
    <div class="container">
        <h1>Modificar Lista Motor</h1>
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Versión</th>
                    <th>Precio por Hora</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['idMotor']; ?></td>
                        <td><?php echo $row['Nombre']; ?></td>
                        <td><?php echo $row['Version']; ?></td>
                        <td><?php echo $row['PrecioH']; ?></td>
                        <td><?php echo $row['Estado']; ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="idMotor" value="<?php echo $row['idMotor']; ?>">
                                <select name="estado">
                                    <option value="Activo" <?php if ($row['Estado'] === 'Activo')
                                        echo 'selected'; ?>>Activo
                                    </option>
                                    <option value="En pruebas" <?php if ($row['Estado'] === 'En pruebas')
                                        echo 'selected'; ?>>
                                        En pruebas</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>