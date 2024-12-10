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
    $_SESSION['error_message'] = 'El grupo no existe o no puedes editarlo.';
    header('Location: gestionar_grupos.php?idOrg=' . $idOrganizacion);
    exit;
}

// Obtener privilegios asociados al grupo
$query_privileges_group = "SELECT idPriv FROM r_grup_priv WHERE idGrup = ?";
$stmt_privileges_group = $conn->prepare($query_privileges_group);
$stmt_privileges_group->bind_param('i', $idGrupo);
$stmt_privileges_group->execute();
$result_privileges_group = $stmt_privileges_group->get_result();

$privileges_group = [];
while ($priv = $result_privileges_group->fetch_assoc()) {
    $privileges_group[] = $priv['idPriv'];
}

// Obtener todos los privilegios disponibles, excluyendo los de contratar PaaS y SaaS
$query_all_privileges = "
    SELECT * FROM privilegio 
    WHERE Nombre NOT IN ('Contratar paas', 'Contratar saas')";
$result_all_privileges = $conn->query($query_all_privileges);

// Gestionar la actualización del grupo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'editar') {
        // Editar el nombre y descripción del grupo
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $selected_privileges = isset($_POST['privilegios']) ? $_POST['privilegios'] : [];

        if (!empty($nombre)) {
            // Actualizar nombre y descripción del grupo
            $query_update_group = "UPDATE grupo SET Nombre = ?, Descripcion = ? WHERE idGrupo = ? AND idOrg = ?";
            $stmt_update_group = $conn->prepare($query_update_group);
            $stmt_update_group->bind_param('ssii', $nombre, $descripcion, $idGrupo, $idOrganizacion);

            if ($stmt_update_group->execute()) {
                // Actualizar privilegios del grupo
                // Primero eliminar todos los privilegios actuales
                $query_delete_privileges = "DELETE FROM r_grup_priv WHERE idGrup = ?";
                $stmt_delete_privileges = $conn->prepare($query_delete_privileges);
                $stmt_delete_privileges->bind_param('i', $idGrupo);
                $stmt_delete_privileges->execute();

                // Insertar los privilegios seleccionados
                foreach ($selected_privileges as $idPriv) {
                    $query_add_privilege = "INSERT INTO r_grup_priv (idGrup, idPriv) VALUES (?, ?)";
                    $stmt_add_privilege = $conn->prepare($query_add_privilege);
                    $stmt_add_privilege->bind_param('ii', $idGrupo, $idPriv);
                    $stmt_add_privilege->execute();
                }

                $_SESSION['success_message'] = 'Grupo actualizado exitosamente.';
                header('Location: gestionar_grupos.php?idOrg=' . $idOrganizacion);
                exit;
            } else {
                $_SESSION['error_message'] = 'Error al actualizar el grupo.';
            }
        } else {
            $_SESSION['error_message'] = 'El nombre del grupo es obligatorio.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <h1 class="h3">Editar Grupo</h1>
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

        <!-- Formulario de edición de grupo -->
        <h2 class="text-center">Editar Grupo</h2>
        <form method="POST">
            <input type="hidden" name="action" value="editar">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Grupo</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($group['Nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($group['Descripcion']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="privilegios" class="form-label">Privilegios Asociados</label>
                <div>
                    <?php while ($priv = $result_all_privileges->fetch_assoc()): ?>
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="privilegio_<?php echo $priv['idPrivilegio']; ?>" 
                                name="privilegios[]" 
                                value="<?php echo $priv['idPrivilegio']; ?>"
                                <?php echo in_array($priv['idPrivilegio'], $privileges_group) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="privilegio_<?php echo $priv['idPrivilegio']; ?>">
                                <?php echo htmlspecialchars($priv['Nombre']); ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
