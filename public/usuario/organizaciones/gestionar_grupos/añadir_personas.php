<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de grupo y organización
if (!isset($_GET['idGrupo']) || !isset($_GET['idOrg']) || empty($_GET['idGrupo']) || empty($_GET['idOrg'])) {
    $_SESSION['error_message'] = 'No se ha especificado el grupo u organización.';
    header('Location: ../ver_organizacion.php');
    exit;
}

$idGrupo = intval($_GET['idGrupo']);
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

// Obtener detalles del grupo
$query_group = "SELECT * FROM grupo WHERE idGrupo = ? AND idOrg = ?";
$stmt_group = $conn->prepare($query_group);
$stmt_group->bind_param('ii', $idGrupo, $idOrganizacion);
$stmt_group->execute();
$result_group = $stmt_group->get_result();
$group = $result_group->fetch_assoc();

if (!$group) {
    $_SESSION['error_message'] = 'No se puede editar este grupo.';
    header('Location: gestionar_grupos.php?idOrg=' . $idOrganizacion);
    exit;
}

// Obtener todos los usuarios que pertenecen a la organización excepto aquellos que ya están en el grupo
$query_users = "
    SELECT u.idUsuario, u.Nombre, u.Email 
    FROM usuario u
    JOIN r_usuario_org ruo ON u.idUsuario = ruo.idUsuario
    WHERE ruo.idOrg = ?
    AND u.idUsuario NOT IN (SELECT idUsuario FROM r_usuario_grupo WHERE idGrupo = ?)";
$stmt_users = $conn->prepare($query_users);
$stmt_users->bind_param('ii', $idOrganizacion, $idGrupo);
$stmt_users->execute();
$result_users = $stmt_users->get_result();

// Gestionar la asignación de usuarios al grupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'asignar') {
    if (isset($_POST['usuarios']) && is_array($_POST['usuarios'])) {
        $usuarios = $_POST['usuarios'];
        $asignaciones = 0;

        foreach ($usuarios as $idUsuario) {
            $query_add_user = "INSERT IGNORE INTO r_usuario_grupo (idUsuario, idGrupo) VALUES (?, ?)";
            $stmt_add_user = $conn->prepare($query_add_user);
            $stmt_add_user->bind_param('ii', $idUsuario, $idGrupo);
            if ($stmt_add_user->execute()) {
                $asignaciones++;
            }
        }

        $_SESSION['success_message'] = "$asignaciones usuarios asignados al grupo.";
        header('Location: editar_grupo.php?idGrupo=' . $idGrupo . '&idOrg=' . $idOrganizacion);
        exit;
    } else {
        $_SESSION['error_message'] = 'Debes seleccionar al menos un usuario.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Personas al Grupo - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <h1 class="h3">Añadir Personas al Grupo</h1>
        <a href="gestionar_grupos.php?idOrg=<?php echo $idOrganizacion; ?>" class="btn btn-outline-light">Volver</a>
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

        <!-- Lista de usuarios y asignación al grupo -->
        <h2 class="text-center mt-5">Usuarios de la Organización</h2>
        <?php if ($result_users->num_rows > 0): ?>
        <form method="POST">
            <input type="hidden" name="action" value="asignar">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Seleccionar</th>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="usuarios[]" value="<?php echo $user['idUsuario']; ?>">
                            </td>
                            <td><?php echo htmlspecialchars($user['idUsuario']); ?></td>
                            <td><?php echo htmlspecialchars($user['Nombre']); ?></td>
                            <td><?php echo htmlspecialchars($user['Email']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-success">Asignar Usuarios al Grupo</button>
        </form>
        <?php else: ?>
            <p class="text-center">No hay usuarios disponibles para asignar a este grupo.</p>
        <?php endif; ?>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
