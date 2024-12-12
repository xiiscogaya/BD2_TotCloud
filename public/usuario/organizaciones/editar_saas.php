<?php
session_start();
include '../../../includes/db_connect.php';

// Validación inicial
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No se ha especificado el SaaS a editar.';
    header('Location: ver_organizacion.php');
    exit;
}

$idSaaS = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Obtener los grupos del usuario
$query_user_group = "
    SELECT g.idGrupo
    FROM grupo g
    JOIN r_usuario_grupo r ON g.idGrupo = r.idGrupo
    WHERE r.idUsuario = ?";
$stmt_user_group = $conn->prepare($query_user_group);
$stmt_user_group->bind_param('i', $user_id);
$stmt_user_group->execute();
$result_user_group = $stmt_user_group->get_result();
$user_groups = array_column($result_user_group->fetch_all(MYSQLI_ASSOC), 'idGrupo');

if (empty($user_groups)) {
    $_SESSION['error_message'] = 'No perteneces a ningún grupo en esta organización.';
    header('Location: ver_organizacion.php');
    exit;
}

// Obtener detalles del SaaS
$query_saas = "
    SELECT s.idSaaS, s.Nombre AS NombreSaaS, s.Usuario, s.Contraseña, s.idPaaS, s.idMotor
    FROM saas s
    WHERE s.idSaaS = ?";
$stmt_saas = $conn->prepare($query_saas);
$stmt_saas->bind_param('i', $idSaaS);
$stmt_saas->execute();
$result_saas = $stmt_saas->get_result();
$saas = $result_saas->fetch_assoc();

if (!$saas) {
    $_SESSION['error_message'] = 'El SaaS no existe.';
    header('Location: ver_organizacion.php');
    exit;
}

// Si hay un motor seleccionado actualmente, obtener su Nombre y Versión
$current_motor_name = '';
$current_motor_version = '';
if (!is_null($saas['idMotor'])) {
    $stmt_motor = $conn->prepare("SELECT Nombre, Version FROM motor WHERE idMotor=?");
    $stmt_motor->bind_param('i', $saas['idMotor']);
    $stmt_motor->execute();
    $res_motor = $stmt_motor->get_result();
    if ($motor_row = $res_motor->fetch_assoc()) {
        $current_motor_name = $motor_row['Nombre'];
        $current_motor_version = $motor_row['Version'];
    }
}

// Obtener todos los nombres de motores distintos
$result_motor_names = $conn->query("SELECT DISTINCT Nombre FROM motor");

// Obtener PaaS disponibles
// Deben ser PaaS del grupo del usuario, que no estén ya asociados a otro SaaS distinto del actual
$placeholders = str_repeat('?,', count($user_groups) - 1) . '?';
$params = array_merge([$idSaaS], $user_groups);
$types = str_repeat('i', count($params));

$query_paas = "
    SELECT DISTINCT p.idPaaS, p.Nombre
    FROM paas p
    JOIN r_paas_grup rpg ON p.idPaaS = rpg.idPaaS
    JOIN grupo g ON rpg.idGrup = g.idGrupo
    LEFT JOIN saas s ON p.idPaaS = s.idPaaS
    WHERE (s.idSaaS = ? OR s.idSaaS IS NULL)
    AND g.idGrupo IN ($placeholders)";
$stmt_paas = $conn->prepare($query_paas);
$stmt_paas->bind_param($types, ...$params);
$stmt_paas->execute();
$result_paas = $stmt_paas->get_result();

// Manejo de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = $_POST['nombre'] ?? '';
    $nueva_contraseña = $_POST['contraseña'] ?? null; // Nueva contraseña opcional
    $nuevo_idPaaS = !empty($_POST['idPaaS']) ? intval($_POST['idPaaS']) : null;
    $nuevo_idMotor = !empty($_POST['motor_version']) ? intval($_POST['motor_version']) : null;

    if (empty($nuevo_nombre)) {
        $_SESSION['error_message'] = 'El nombre del SaaS no puede estar vacío.';
        header('Location: editar_saas.php?id=' . $idSaaS);
        exit;
    }

    $conn->begin_transaction();
    try {
        if (!empty($nueva_contraseña)) {
            // Hashear nueva contraseña si se proporciona
            $hashed_password = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
            $query_update = "UPDATE saas SET Nombre = ?, Contraseña = ?, idPaaS = ?, idMotor = ? WHERE idSaaS = ?";
            $stmt_update = $conn->prepare($query_update);
            $stmt_update->bind_param('ssiii', $nuevo_nombre, $hashed_password, $nuevo_idPaaS, $nuevo_idMotor, $idSaaS);
        } else {
            // Actualizar sin modificar la contraseña si está vacía
            $query_update = "UPDATE saas SET Nombre = ?, idPaaS = ?, idMotor = ? WHERE idSaaS = ?";
            $stmt_update = $conn->prepare($query_update);
            $stmt_update->bind_param('siii', $nuevo_nombre, $nuevo_idPaaS, $nuevo_idMotor, $idSaaS);
        }
        $stmt_update->execute();

        $conn->commit();
        $_SESSION['success_message'] = 'El SaaS fue actualizado correctamente.';
        header('Location: ver_organizacion.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = 'Error al actualizar el SaaS: ' . $e->getMessage();
        header('Location: editar_saas.php?id=' . $idSaaS);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar SaaS - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadVersions(motorName) {
            const versionSelect = document.getElementById('motor_version');
            versionSelect.innerHTML = '';

            if (!motorName) {
                versionSelect.innerHTML = '<option value="">Seleccione un motor primero</option>';
                return;
            }

            fetch(`get_versions.php?nombre=${encodeURIComponent(motorName)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.versions && data.versions.length > 0) {
                        data.versions.forEach(v => {
                            const option = document.createElement('option');
                            option.value = v.idMotor;
                            option.textContent = v.version;
                            versionSelect.appendChild(option);
                        });
                    } else {
                        versionSelect.innerHTML = '<option value="">Sin versiones disponibles</option>';
                    }
                })
                .catch(error => {
                    console.error('Error al cargar las versiones:', error);
                    versionSelect.innerHTML = '<option value="">Error al cargar versiones</option>';
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const currentMotorName = <?php echo json_encode($current_motor_name); ?>;
            const currentMotorVersion = <?php echo json_encode($current_motor_version); ?>;
            const current_idMotor = <?php echo json_encode($saas['idMotor']); ?>;

            if (currentMotorName) {
                loadVersions(currentMotorName);
                // Esperar medio segundo a que se carguen las versiones
                setTimeout(() => {
                    if (current_idMotor) {
                        const versionSelect = document.getElementById('motor_version');
                        for (let i=0; i<versionSelect.options.length; i++) {
                            if (parseInt(versionSelect.options[i].value) === parseInt(current_idMotor)) {
                                versionSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }, 500);
            } else {
                document.getElementById('motor_version').innerHTML = '<option value="">Seleccione un motor primero</option>';
            }
        });
    </script>
</head>
<body>
    <header class="bg-primary text-white p-3">
        <h1 class="h3">Editar SaaS</h1>
        <a href="ver_organizacion.php" class="btn btn-outline-light">Volver</a>
    </header>

    <main class="container my-5">
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

        <form method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del SaaS</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($saas['NombreSaaS']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Nueva Contraseña</label>
                <input type="password" class="form-control" id="contraseña" name="contraseña">
                <small class="text-muted">Deja este campo vacío para mantener la contraseña actual.</small>
            </div>
            <div class="mb-3">
                <label for="idPaaS" class="form-label">PaaS Asociado</label>
                <select class="form-select" id="idPaaS" name="idPaaS">
                    <option value="" <?php echo is_null($saas['idPaaS']) ? 'selected' : ''; ?>>Sin PaaS asociado</option>
                    <?php while ($p = $result_paas->fetch_assoc()): ?>
                        <option value="<?php echo $p['idPaaS']; ?>" <?php echo ($p['idPaaS'] == $saas['idPaaS']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['Nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="motor_name" class="form-label">Motor</label>
                <select class="form-select" id="motor_name" name="motor_name" onchange="loadVersions(this.value)">
                    <option value="">Sin motor asociado</option>
                    <?php 
                    // Regeneramos el cursor de $result_motor_names si fuera necesario
                    $result_motor_names->data_seek(0);
                    while ($mn = $result_motor_names->fetch_assoc()):
                    ?>
                        <option value="<?php echo htmlspecialchars($mn['Nombre']); ?>" <?php echo ($mn['Nombre'] === $current_motor_name) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mn['Nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="motor_version" class="form-label">Versión</label>
                <select class="form-select" id="motor_version" name="motor_version">
                    <option value="">Seleccione un motor primero</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </main>
</body>
</html>
