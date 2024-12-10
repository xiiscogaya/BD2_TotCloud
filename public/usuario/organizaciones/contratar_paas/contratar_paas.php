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

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPaaS = intval($_POST['idPaaS']);
    $nombre = trim($_POST['nombre']);
    $idSO = intval($_POST['idSO']);
    $idIp = intval($_POST['idIp']);

    if (empty($nombre) || $idPaaS <= 0 || $idSO <= 0 || $idIp <= 0) {
        $_SESSION['error_message'] = 'Todos los campos son obligatorios.';
    } else {
        $conn->begin_transaction();
        try {
            // Actualizar el nombre, sistema operativo e IP del PaaS seleccionado
            $update_paas_query = "UPDATE paas SET Nombre = ?, Estado = 'Activo', idSO = ? WHERE idPaaS = ?";
            $stmt_update_paas = $conn->prepare($update_paas_query);
            $stmt_update_paas->bind_param('sii', $nombre, $idSO, $idPaaS);
            if (!$stmt_update_paas->execute()) {
                throw new Exception('Error al actualizar el PaaS.');
            }

            // Asociar la IP con el PaaS
            $update_ip_query = "UPDATE direccionip SET idPaaS = ? WHERE idIp = ?";
            $stmt_update_ip = $conn->prepare($update_ip_query);
            $stmt_update_ip->bind_param('ii', $idPaaS, $idIp);
            if (!$stmt_update_ip->execute()) {
                throw new Exception('Error al asociar la IP con el PaaS.');
            }

            // Asociar el PaaS con el grupo admin de la organización
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
                if (!$stmt_relation->execute()) {
                    throw new Exception('Error al asociar el PaaS con el grupo admin.');
                }
            } else {
                throw new Exception('No se encontró el grupo admin para la organización.');
            }

            $conn->commit();
            $_SESSION['success_message'] = 'PaaS creado exitosamente.';
            header('Location: ../ver_organizacion.php?id=' . $idOrganizacion);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = $e->getMessage();
        }
    }
}

// Obtener sistemas operativos disponibles
$query_sos = "SELECT * FROM sistemaoperativo";
$result_sos = $conn->query($query_sos);

// Obtener direcciones IP disponibles
$query_ips = "SELECT * FROM direccionip WHERE idPaaS IS NULL";
$result_ips = $conn->query($query_ips);

// Obtener PaaS disponibles con detalles
$query_paas = "
    SELECT p.*, 
           GROUP_CONCAT(DISTINCT CONCAT(c.Nombre, ' (Cantidad: ', rpc.Cantidad, ')') SEPARATOR ', ') AS CPUs,
           GROUP_CONCAT(DISTINCT CONCAT(r.Nombre, ' (Cantidad: ', rpr.Cantidad, ')') SEPARATOR ', ') AS RAMs,
           GROUP_CONCAT(DISTINCT CONCAT(a.Nombre, ' (Cantidad: ', rpa.Cantidad, ')') SEPARATOR ', ') AS Almacenamientos
    FROM paas p
    LEFT JOIN r_paas_cpu rpc ON p.idPaaS = rpc.idPaaS
    LEFT JOIN cpu c ON rpc.idCPU = c.idCPU
    LEFT JOIN r_paas_ram rpr ON p.idPaaS = rpr.idPaaS
    LEFT JOIN ram r ON rpr.idRAM = r.idRAM
    LEFT JOIN r_paas_almacenamiento rpa ON p.idPaaS = rpa.idPaaS
    LEFT JOIN almacenamiento a ON rpa.idAlmacenamiento = a.idAlmacenamiento
    WHERE p.Estado = 'Disponible'
    GROUP BY p.idPaaS";
$result_paas = $conn->query($query_paas);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear PaaS - TotCloud</title>
    <link href="../../../css/estilos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white text-center py-3">
        <h1>Crear PaaS</h1>
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

        <form method="POST" action="">
            <div class="mb-3">
                <label for="idPaaS" class="form-label">Seleccionar PaaS Disponible</label>
                <select class="form-select" id="idPaaS" name="idPaaS" required>
                    <option value="">Seleccione un PaaS</option>
                    <?php while ($paas = $result_paas->fetch_assoc()): ?>
                        <option value="<?php echo $paas['idPaaS']; ?>">
                            <?php echo htmlspecialchars($paas['Nombre']); ?> - CPUs: <?php echo htmlspecialchars($paas['CPUs'] ?: 'N/A'); ?>,
                            RAMs: <?php echo htmlspecialchars($paas['RAMs'] ?: 'N/A'); ?>,
                            Almacenamientos: <?php echo htmlspecialchars($paas['Almacenamientos'] ?: 'N/A'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del PaaS</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>

            <div class="mb-3">
                <label for="idSO" class="form-label">Sistema Operativo</label>
                <select class="form-select" id="idSO" name="idSO" required>
                    <option value="">Seleccione un sistema operativo</option>
                    <?php while ($so = $result_sos->fetch_assoc()): ?>
                        <option value="<?php echo $so['idSO']; ?>">
                            <?php echo htmlspecialchars($so['Nombre'] . ' ' . $so['Version']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="idIp" class="form-label">Dirección IP</label>
                <select class="form-select" id="idIp" name="idIp" required>
                    <option value="">Seleccione una dirección IP</option>
                    <?php while ($ip = $result_ips->fetch_assoc()): ?>
                        <option value="<?php echo $ip['idIp']; ?>">
                            <?php echo htmlspecialchars($ip['Direccion']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Crear PaaS</button>
            </div>
        </form>
    </main>

    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
