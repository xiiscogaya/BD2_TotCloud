<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Validar si se ha proporcionado el ID del PaaS
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No se ha especificado el PaaS a editar.';
    header('Location: ver_organizacion.php');
    exit;
}

$idPaaS = intval($_GET['id']);

// Obtener detalles del PaaS
$query_paas = "
    SELECT p.idPaaS, p.Nombre AS NombrePaaS, p.Estado, so.idSO, so.Nombre AS NombreSO 
    FROM paas p 
    LEFT JOIN sistemaoperativo so ON p.idSO = so.idSO 
    WHERE p.idPaaS = ?";
$stmt_paas = $conn->prepare($query_paas);
$stmt_paas->bind_param('i', $idPaaS);
$stmt_paas->execute();
$result_paas = $stmt_paas->get_result();
$paas = $result_paas->fetch_assoc();

if (!$paas) {
    $_SESSION['error_message'] = 'El PaaS no existe.';
    header('Location: ver_organizacion.php');
    exit;
}

// Obtener lista de sistemas operativos disponibles
$query_sos = "SELECT idSO, Nombre FROM sistemaoperativo";
$result_sos = $conn->query($query_sos);

// Manejar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = $_POST['nombre'] ?? '';
    $nuevo_idSO = $_POST['idSO'] ?? null;

    // Validar datos
    if (empty($nuevo_nombre)) {
        $_SESSION['error_message'] = 'El nombre del PaaS no puede estar vacío.';
        header('Location: editar_paas.php?id=' . $idPaaS);
        exit;
    }

    try {
        $conn->begin_transaction();

        // Actualizar el PaaS
        $query_update = "UPDATE paas SET Nombre = ?, idSO = ? WHERE idPaaS = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param('sii', $nuevo_nombre, $nuevo_idSO, $idPaaS);
        $stmt_update->execute();

        $conn->commit();

        $_SESSION['success_message'] = 'El PaaS fue actualizado correctamente.';
        header('Location: ver_organizacion.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = 'Error al actualizar el PaaS: ' . $e->getMessage();
        header('Location: editar_paas.php?id=' . $idPaaS);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar PaaS - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <h1 class="h3">Editar PaaS</h1>
        <a href="ver_organizacion.php" class="btn btn-outline-light">Volver</a>
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

        <!-- Formulario de edición -->
        <form method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del PaaS</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($paas['NombrePaaS']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="idSO" class="form-label">Sistema Operativo</label>
                <select class="form-select" id="idSO" name="idSO">
                    <option value="" <?php echo is_null($paas['idSO']) ? 'selected' : ''; ?>>Sin sistema operativo</option>
                    <?php while ($so = $result_sos->fetch_assoc()): ?>
                        <option value="<?php echo $so['idSO']; ?>" <?php echo $so['idSO'] == $paas['idSO'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($so['Nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </main>
</body>
</html>
