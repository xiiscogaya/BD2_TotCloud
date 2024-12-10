<?php
session_start();
include '../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$error = ''; // Inicializar el mensaje de error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar datos del formulario
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $idCreador = $_SESSION['user_id'];

    if (empty($nombre)) {
        $error = 'El nombre de la organización es obligatorio.';
    } else {
        // Generar el próximo ID disponible para la organización
        $query_next_org_id = "SELECT COALESCE(MAX(idOrganizacion), 0) + 1 AS next_id FROM organizacion";
        $result_next_org_id = $conn->query($query_next_org_id);
        $next_org_id = $result_next_org_id->fetch_assoc()['next_id'];

        // Insertar la nueva organización
        $insert_org_query = "INSERT INTO organizacion (idOrganizacion, Nombre, Descripcion, idCreador) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_org_query);
        $stmt->bind_param('issi', $next_org_id, $nombre, $descripcion, $idCreador);

        if ($stmt->execute()) {
            // Vincular al usuario con la organización en la tabla r_usuario_org
            $link_org_query = "INSERT INTO r_usuario_org (idUsuario, idOrg) VALUES (?, ?)";
            $stmt_link_org = $conn->prepare($link_org_query);
            $stmt_link_org->bind_param('ii', $idCreador, $next_org_id);

            if ($stmt_link_org->execute()) {
                // Generar el próximo ID disponible para el grupo
                $query_next_group_id = "SELECT COALESCE(MAX(idGrupo), 0) + 1 AS next_id FROM grupo";
                $result_next_group_id = $conn->query($query_next_group_id);
                $next_group_id = $result_next_group_id->fetch_assoc()['next_id'];

                // Crear un grupo "admin" asociado a la nueva organización
                $insert_group_query = "INSERT INTO grupo (idGrupo, Nombre, Descripcion, idOrg) VALUES (?, 'admin', 'Grupo con todos los permisos', ?)";
                $stmt_group = $conn->prepare($insert_group_query);
                $stmt_group->bind_param('ii', $next_group_id, $next_org_id);

                if ($stmt_group->execute()) {
                    // Obtener todos los privilegios disponibles (idPrivilegio)
                    $privileges_query = "SELECT idPrivilegio FROM privilegio";
                    $privileges_result = $conn->query($privileges_query);

                    $all_privileges_added = true;
                    while ($privilege = $privileges_result->fetch_assoc()) {
                        // Insertar cada privilegio en r_grup_priv para el grupo "admin"
                        $insert_priv_query = "INSERT INTO r_grup_priv (idGrup, idPriv) VALUES (?, ?)";
                        $stmt_priv = $conn->prepare($insert_priv_query);
                        $stmt_priv->bind_param('ii', $next_group_id, $privilege['idPrivilegio']);

                        if (!$stmt_priv->execute()) {
                            $all_privileges_added = false;
                            break;
                        }
                    }

                    if ($all_privileges_added) {
                        // Insertar el usuario creador en el grupo "admin"
                        $insert_usuario_grupo = "INSERT INTO r_usuario_grupo (idUsuario, idGrupo) VALUES (?, ?)";
                        $stmt_usuario_grupo = $conn->prepare($insert_usuario_grupo);
                        $stmt_usuario_grupo->bind_param('ii', $idCreador, $next_group_id);

                        if ($stmt_usuario_grupo->execute()) {
                            $_SESSION['success_message'] = 'Organización y grupo "admin" creados exitosamente.';
                            header('Location: usuario.php');
                            exit;
                        } else {
                            $error = 'Error al vincular al usuario con el grupo admin.';
                        }
                    } else {
                        $error = 'Error al asignar privilegios al grupo admin.';
                    }
                } else {
                    $error = 'Error al crear el grupo admin para la organización.';
                }
            } else {
                $error = 'Error al vincular la organización con el usuario.';
            }
        } else {
            $error = 'Error al crear la organización.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Organización - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <!-- Archivo de estilos personalizados -->
   <link href="../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Crear Organización</h1>
        </div>
        <div>
            <a href="../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Nueva Organización</h2>

        <!-- Botón para regresar -->
        <div class="container my-3">
            <a href="usuario.php" class="btn btn-secondary">Volver</a>
        </div>

        <!-- Mostrar mensaje de error -->
        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para crear organización -->
        <form method="POST" action="crear_organizacion.php" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de la Organización</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Crear Organización</button>
        </form>
    </main>

    <!-- Pie de página -->
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
