<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de organización
if (!isset($_GET['idOrg']) || empty($_GET['idOrg'])) {
    $_SESSION['error_message'] = 'No se ha especificado la organización.';
    header('Location: ../ver_organizacion.php');
    exit;
}

$idOrganizacion = intval($_GET['idOrg']);
$user_id = $_SESSION['user_id'];

// Verificar que el usuario tiene acceso a esta organización
$query_check = "SELECT * FROM r_usuario_org WHERE idUsuario = ? AND idOrg = ?";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param('ii', $user_id, $idOrganizacion);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    $_SESSION['error_message'] = 'No tienes acceso a esta organización.';
    header('Location: ../ver_organizacion.php');
    exit;
}

// Obtener los grupos a los que pertenece el usuario en esta organización
$query_groups = "
    SELECT g.idGrupo, g.Nombre, g.Descripcion 
    FROM grupo g
    JOIN r_usuario_grupo rug ON g.idGrupo = rug.idGrupo
    WHERE g.idOrg = ? AND rug.idUsuario = ?";
$stmt_groups = $conn->prepare($query_groups);
$stmt_groups->bind_param('ii', $idOrganizacion, $user_id);
$stmt_groups->execute();
$result_groups = $stmt_groups->get_result();

// Gestionar creación de grupos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    if (!empty($nombre)) {
        // Obtener el próximo ID para el grupo
        $query_next_group_id = "SELECT COALESCE(MAX(idGrupo), 0) + 1 AS next_id FROM grupo";
        $result_next_group_id = $conn->query($query_next_group_id);
        $next_group_id = $result_next_group_id->fetch_assoc()['next_id'];

        // Insertar el nuevo grupo
        $query_create_group = "INSERT INTO grupo (idGrupo, Nombre, Descripcion, idOrg) VALUES (?, ?, ?, ?)";
        $stmt_create_group = $conn->prepare($query_create_group);
        $stmt_create_group->bind_param('issi', $next_group_id, $nombre, $descripcion, $idOrganizacion);

        if ($stmt_create_group->execute()) {
            // Relacionar al creador del grupo en la tabla r_usuario_grupo
            $query_add_creator = "INSERT INTO r_usuario_grupo (idUsuario, idGrupo) VALUES (?, ?)";
            $stmt_add_creator = $conn->prepare($query_add_creator);
            $stmt_add_creator->bind_param('ii', $user_id, $next_group_id);

            if ($stmt_add_creator->execute()) {
                $_SESSION['success_message'] = 'Grupo creado exitosamente y asignado al usuario.';
            } else {
                $_SESSION['error_message'] = 'El grupo fue creado, pero no se pudo asignar al usuario.';
            }
        } else {
            $_SESSION['error_message'] = 'Error al crear el grupo.';
        }
    } else {
        $_SESSION['error_message'] = 'El nombre del grupo es obligatorio.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Grupos - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <h1 class="h3">Gestionar Grupos</h1>
        <a href="../ver_organizacion.php?id=<?php echo $idOrganizacion; ?>" class="btn btn-outline-light">Volver</a>
    </header>

    <main class="container my-5">
        <!-- Mensajes -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Lista de grupos -->
        <h2>Mis Grupos en la Organización</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($group = $result_groups->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($group['idGrupo']); ?></td>
                        <td><?php echo htmlspecialchars($group['Nombre']); ?></td>
                        <td><?php echo htmlspecialchars($group['Descripcion']); ?></td>
                        <td>
                            <?php if ($group['Nombre'] === 'admin'): ?>
                                <!-- Solo permitir añadir personas para el grupo admin -->
                                <a href="añadir_personas.php?idGrupo=<?php echo $group['idGrupo']; ?>&idOrg=<?php echo $idOrganizacion; ?>" class="btn btn-success btn-sm">Añadir Personas</a>
                            <?php else: ?>
                                <!-- Permitir editar, eliminar y añadir personas para otros grupos -->
                                <a href="editar_grupo.php?idGrupo=<?php echo $group['idGrupo']; ?>&idOrg=<?php echo $idOrganizacion; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="añadir_personas.php?idGrupo=<?php echo $group['idGrupo']; ?>&idOrg=<?php echo $idOrganizacion; ?>" class="btn btn-success btn-sm">Añadir Personas</a>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="idGrupo" value="<?php echo $group['idGrupo']; ?>">
                                    <input type="hidden" name="nombreGrupo" value="<?php echo $group['Nombre']; ?>">
                                    <input type="hidden" name="action" value="eliminar">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este grupo?');">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Formulario para crear grupos -->
        <h2 class="mt-4">Crear Nuevo Grupo</h2>
        <form method="POST">
            <input type="hidden" name="action" value="crear">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Grupo</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Crear Grupo</button>
        </form>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
