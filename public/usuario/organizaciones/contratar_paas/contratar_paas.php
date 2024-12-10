<?php
session_start();
include '../../../../includes/db_connect.php'; // Ajustar ruta a la conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Verificar si se ha proporcionado un ID de organización
if (!isset($_GET['idOrg']) || empty($_GET['idOrg'])) {
    $_SESSION['error_message'] = 'No se ha especificado la organización.';
    header('Location: ../../usuario.php');
    exit;
}

$idOrganizacion = intval($_GET['idOrg']);

// Verificar que el usuario tiene acceso a esta organización
$query_check = "SELECT * FROM r_usuario_org WHERE idUsuario = ? AND idOrg = ?";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param('ii', $user_id, $idOrganizacion);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    $_SESSION['error_message'] = 'No tienes acceso a esta organización.';
    header('Location: ../../usuario.php');
    exit;
}

// Si se envió el formulario para contratar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPaaS'])) {
    $idPaaS = intval($_POST['idPaaS']);

    // Verificar que este PaaS está disponible para ser contratado
    $query_paas_check = "SELECT * FROM paas WHERE idPaaS = ? AND Estado = 'Disponible'";
    $stmt_paas_check = $conn->prepare($query_paas_check);
    $stmt_paas_check->bind_param('i', $idPaaS);
    $stmt_paas_check->execute();
    $result_paas_check = $stmt_paas_check->get_result();

    if ($result_paas_check->num_rows > 0) {
        // Actualizar el estado del PaaS a "Activo"
        $update_query = "UPDATE paas SET Estado = 'Activo' WHERE idPaaS = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param('i', $idPaaS);

        // Asociar el PaaS con la organización en r_paas_grup
        $query_get_admin_group = "SELECT idGrupo FROM grupo WHERE idOrg = ? AND Nombre = 'admin'";
        $stmt_get_admin_group = $conn->prepare($query_get_admin_group);
        $stmt_get_admin_group->bind_param('i', $idOrganizacion);
        $stmt_get_admin_group->execute();
        $result_admin_group = $stmt_get_admin_group->get_result();

        if ($result_admin_group->num_rows > 0) {
            $admin_group = $result_admin_group->fetch_assoc();
            $idAdminGroup = $admin_group['idGrupo'];

            $insert_relation = "INSERT INTO r_paas_grup (idPaaS, idGrup) VALUES (?, ?)";
            $stmt_relation = $conn->prepare($insert_relation);
            $stmt_relation->bind_param('ii', $idPaaS, $idAdminGroup);

            if ($stmt_update->execute() && $stmt_relation->execute()) {
                $_SESSION['success_message'] = 'PaaS contratado exitosamente.';
                header('Location: ../ver_organizacion.php?id=' . $idOrganizacion);
                exit;
            } else {
                $_SESSION['error_message'] = 'Error al contratar el PaaS.';
            }
        } else {
            $_SESSION['error_message'] = 'No se encontró el grupo admin para la organización.';
        }
    } else {
        $_SESSION['error_message'] = 'El PaaS seleccionado no está disponible.';
    }
}

// Obtener lista de PaaS disponibles
$query_paas = "SELECT * FROM paas WHERE Estado = 'Disponible'";
$result_paas = $conn->query($query_paas);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratar PaaS - TotCloud</title>
    <link href="../../../css/estilos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <h1 class="h3 mb-0">Contratar PaaS</h1>
        <a href="../ver_organizacion.php?id=<?php echo $idOrganizacion; ?>" class="btn btn-outline-light">Volver a la Organización</a>
    </header>

    <main class="container my-5">
        <!-- Mensajes de éxito o error -->
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

        <h2 class="text-center mb-4">Selecciona un PaaS para Contratar</h2>
        
        <?php if ($result_paas->num_rows > 0): ?>
            <form method="POST">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Seleccionar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($paas = $result_paas->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($paas['idPaaS']); ?></td>
                                <td><?php echo htmlspecialchars($paas['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($paas['Estado']); ?></td>
                                <td>
                                    <input type="radio" name="idPaaS" value="<?php echo $paas['idPaaS']; ?>">
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Contratar</button>
                </div>
            </form>
        <?php else: ?>
            <p class="text-center">No hay PaaS disponibles para contratar.</p>
        <?php endif; ?>
    </main>

    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
