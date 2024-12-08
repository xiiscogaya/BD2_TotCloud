<?php
session_start();
include '../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Obtener todas las configuraciones PaaS
$query = "SELECT * FROM paas";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Lista PaaS - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white text-center py-3">
        <h1>Modificar Lista PaaS</h1>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <!-- Botón para crear nueva configuración PaaS -->
        <div class="text-end mb-3">
            <a href="crear_paas.php" class="btn btn-success">Crear nueva configuración PaaS</a>
        </div>

        <!-- Lista de configuraciones PaaS -->
        <h2 class="mb-4">Configuraciones PaaS existentes</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Sistema Operativo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['idPaaS']); ?></td>
                            <td><?php echo htmlspecialchars($row['Nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['Estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['idSO']); ?></td>
                            <td>
                                <a href="editar_paas.php?id=<?php echo $row['idPaaS']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                <a href="eliminar_paas.php?id=<?php echo $row['idPaaS']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta configuración PaaS?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No hay configuraciones PaaS registradas.</p>
        <?php endif; ?>
    </main>

    <!-- Pie de página -->
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
