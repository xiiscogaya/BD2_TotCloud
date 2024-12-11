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

// Obtener nombres únicos de motores
$query_motor_names = "SELECT DISTINCT Nombre FROM motor";
$result_motor_names = $conn->query($query_motor_names);

// Manejar el formulario de creación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_saas = trim($_POST['nombre_saas']);
    $usuario_saas = $_SESSION['user_name']; // Nombre de usuario actual
    $contrasena = trim($_POST['contrasena']);
    $idMotor = intval($_POST['idMotor']);

    if (empty($nombre_saas) || empty($contrasena) || $idMotor <= 0) {
        $_SESSION['error_message'] = 'Todos los campos son obligatorios.';
    } else {
        $conn->begin_transaction();
        try {
            // Crear la instancia SaaS
            $query_create_saas = "INSERT INTO saas (Nombre, Usuario, Contraseña, idPaaS, idMotor) VALUES (?, ?, ?, NULL, ?)";
            $stmt_create_saas = $conn->prepare($query_create_saas);
            $stmt_create_saas->bind_param('sssi', $nombre_saas, $usuario_saas, $contrasena, $idMotor);

            if (!$stmt_create_saas->execute()) {
                throw new Exception('Error al contratar el SaaS.');
            }

            // Obtener el idSaaS recién creado
            $idSaaS = $conn->insert_id;

            // Asociar el SaaS con el grupo admin de la organización
            $query_get_admin_group = "SELECT idGrupo FROM grupo WHERE idOrg = ? AND Nombre = 'admin'";
            $stmt_get_admin_group = $conn->prepare($query_get_admin_group);
            $stmt_get_admin_group->bind_param('i', $idOrganizacion);
            $stmt_get_admin_group->execute();
            $result_admin_group = $stmt_get_admin_group->get_result();

            if ($result_admin_group->num_rows > 0) {
                $admin_group = $result_admin_group->fetch_assoc();
                $idAdminGroup = $admin_group['idGrupo'];

                $insert_relation = "INSERT INTO r_saas_grup (idSaaS, idGrup) VALUES (?, ?)";
                $stmt_relation = $conn->prepare($insert_relation);
                $stmt_relation->bind_param('ii', $idSaaS, $idAdminGroup);
                if (!$stmt_relation->execute()) {
                    throw new Exception('Error al asociar el SaaS con el grupo admin.');
                }
            } else {
                throw new Exception('No se encontró el grupo admin para la organización.');
            }

            $conn->commit();
            $_SESSION['success_message'] = 'SaaS contratado exitosamente.';
            header('Location: ../ver_organizacion.php?id=' . $idOrganizacion);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contratar SaaS - TotCloud</title>
    <link href="../../../css/estilos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function fetchMotorVersions() {
            const nombre = document.getElementById('motor_name').value;
            const versionSelect = document.getElementById('motor_version');

            if (nombre) {
                fetch(`fetch_motor_versions.php?nombre=${encodeURIComponent(nombre)}`)
                    .then(response => response.json())
                    .then(data => {
                        versionSelect.innerHTML = '<option value="">Seleccione una versión</option>';
                        data.forEach(version => {
                            const option = document.createElement('option');
                            option.value = version.idMotor;
                            option.textContent = version.Version;
                            versionSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error al cargar las versiones:', error));
            } else {
                versionSelect.innerHTML = '<option value="">Seleccione un motor primero</option>';
            }
        }
    </script>
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white text-center py-3">
        <h1>Contratar SaaS</h1>
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

        <form method="POST">
            <div class="mb-3">
                <label for="nombre_saas" class="form-label">Nombre del SaaS</label>
                <input type="text" class="form-control" id="nombre_saas" name="nombre_saas" required>
            </div>

            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
            </div>

            <div class="mb-3">
                <label for="motor_name" class="form-label">Seleccione un motor</label>
                <select class="form-select" id="motor_name" name="motor_name" onchange="fetchMotorVersions()" required>
                    <option value="">Seleccione un motor</option>
                    <?php while ($motor = $result_motor_names->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($motor['Nombre']); ?>">
                            <?php echo htmlspecialchars($motor['Nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="motor_version" class="form-label">Seleccione una versión</label>
                <select class="form-select" id="motor_version" name="idMotor" required>
                    <option value="">Seleccione un motor primero</option>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Contratar SaaS</button>
            </div>
        </form>
    </main>

    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
