<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

// Validación inicial
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

if (!isset($_GET['idGrupo']) || !isset($_GET['idOrg']) || empty($_GET['idGrupo']) || empty($_GET['idOrg'])) {
    $_SESSION['error_message'] = 'No se ha especificado el grupo u organización.';
    header('Location: ../ver_organizacion.php');
    exit;
}

$idGrupo = intval($_GET['idGrupo']);
$idOrganizacion = intval($_GET['idOrg']);
$user_id = $_SESSION['user_id'];

// Verificar acceso del usuario a la organización
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

// Obtener privilegios del grupo
$query_privileges_group = "SELECT idPriv FROM r_grup_priv WHERE idGrup = ?";
$stmt_privileges_group = $conn->prepare($query_privileges_group);
$stmt_privileges_group->bind_param('i', $idGrupo);
$stmt_privileges_group->execute();
$result_privileges_group = $stmt_privileges_group->get_result();

$privileges_group = [];
while ($priv = $result_privileges_group->fetch_assoc()) {
    $privileges_group[] = $priv['idPriv'];
}

// Obtener todos los privilegios disponibles
$query_all_privileges = "
    SELECT * FROM privilegio 
    WHERE Nombre NOT IN ('Contratar paas', 'Contratar saas')";
$result_all_privileges = $conn->query($query_all_privileges);

// Obtener SaaS disponibles del grupo admin
$query_admin_saas = "
    SELECT s.idSaaS, s.Nombre AS SaaS, p.idPaaS, p.Nombre AS PaaS
    FROM saas s
    JOIN paas p ON s.idPaaS = p.idPaaS
    JOIN r_saas_grup rsg ON rsg.idSaaS = s.idSaaS
    JOIN grupo g ON rsg.idGrup = g.idGrupo
    WHERE g.idOrg = ? AND g.Nombre = 'admin'";
$stmt_admin_saas = $conn->prepare($query_admin_saas);
$stmt_admin_saas->bind_param('i', $idOrganizacion);
$stmt_admin_saas->execute();
$result_saas = $stmt_admin_saas->get_result();

// Obtener PaaS disponibles del grupo admin
$query_admin_paas = "
    SELECT p.idPaaS, p.Nombre
    FROM paas p
    JOIN r_paas_grup rpg ON p.idPaaS = rpg.idPaaS
    JOIN grupo g ON rpg.idGrup = g.idGrupo
    WHERE g.idOrg = ? AND g.Nombre = 'admin'";
$stmt_admin_paas = $conn->prepare($query_admin_paas);
$stmt_admin_paas->bind_param('i', $idOrganizacion);
$stmt_admin_paas->execute();
$result_paas = $stmt_admin_paas->get_result();

// Obtener SaaS y PaaS vinculados al grupo actual
$query_saas_group = "SELECT idSaaS FROM r_saas_grup WHERE idGrup = ?";
$stmt_saas_group = $conn->prepare($query_saas_group);
$stmt_saas_group->bind_param('i', $idGrupo);
$stmt_saas_group->execute();
$result_saas_group = $stmt_saas_group->get_result();
$saas_group = array_column($result_saas_group->fetch_all(MYSQLI_ASSOC), 'idSaaS');

$query_paas_group = "SELECT idPaaS FROM r_paas_grup WHERE idGrup = ?";
$stmt_paas_group = $conn->prepare($query_paas_group);
$stmt_paas_group->bind_param('i', $idGrupo);
$stmt_paas_group->execute();
$result_paas_group = $stmt_paas_group->get_result();
$paas_group = array_column($result_paas_group->fetch_all(MYSQLI_ASSOC), 'idPaaS');


// Manejo de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar') {
    $selected_saas = $_POST['saas'] ?? [];
    $selected_paas = $_POST['paas'] ?? [];

    $conn->begin_transaction();

    try {
        // Actualizar SaaS
        $conn->query("DELETE FROM r_saas_grup WHERE idGrup = $idGrupo");
        foreach ($selected_saas as $idSaaS) {
            $conn->query("INSERT INTO r_saas_grup (idSaaS, idGrup) VALUES ($idSaaS, $idGrupo)");

            // Insertar PaaS vinculado al SaaS seleccionado
            $result_paas_linked = $conn->query("SELECT idPaaS FROM saas WHERE idSaaS = $idSaaS");
            while ($row = $result_paas_linked->fetch_assoc()) {
                $conn->query("INSERT IGNORE INTO r_paas_grup (idPaaS, idGrup) VALUES ({$row['idPaaS']}, $idGrupo)");
            }
        }

        // Actualizar PaaS
        $conn->query("DELETE FROM r_paas_grup WHERE idGrup = $idGrupo");
        foreach ($selected_paas as $idPaaS) {
            $conn->query("INSERT INTO r_paas_grup (idPaaS, idGrup) VALUES ($idPaaS, $idGrupo)");
        }

        $conn->commit();
        $_SESSION['success_message'] = 'Grupo actualizado correctamente.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = 'Error al actualizar el grupo: ' . $e->getMessage();
    }

    header('Location: gestionar_grupos.php?idOrg=' . $idOrganizacion);
    exit;
}
?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        <form id="editGroupForm" method="POST">
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
            <div class="mb-3">
                <label for="paas" class="form-label">PaaS Asociados</label>
                <div>
                    <?php while ($paas = $result_paas->fetch_assoc()): ?>
                        <div class="form-check">
                            <input 
                                class="form-check-input paas-check" 
                                type="checkbox" 
                                id="paas_<?php echo $paas['idPaaS']; ?>" 
                                name="paas[]" 
                                value="<?php echo $paas['idPaaS']; ?>"
                                <?php echo in_array($paas['idPaaS'], $paas_group) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="paas_<?php echo $paas['idPaaS']; ?>">
                                <?php echo htmlspecialchars($paas['Nombre']); ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="mb-3">
                <label for="saas" class="form-label">SaaS Asociados</label>
                <div>
                    <?php while ($saas = $result_saas->fetch_assoc()): ?>
                        <div class="form-check">
                            <input 
                                class="form-check-input saas-check" 
                                type="checkbox" 
                                id="saas_<?php echo $saas['idSaaS']; ?>" 
                                name="saas[]" 
                                value="<?php echo $saas['idSaaS']; ?>" 
                                data-paas-id="<?php echo $saas['idPaaS']; ?>"
                                data-paas-name="<?php echo htmlspecialchars($saas['PaaS']); ?>"
                                <?php echo in_array($saas['idSaaS'], $saas_group) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="saas_<?php echo $saas['idSaaS']; ?>">
                                <?php echo htmlspecialchars($saas['SaaS']); ?> (PaaS: <?php echo htmlspecialchars($saas['PaaS']); ?>)
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <button type="button" class="btn btn-primary" id="confirmChanges">Confirmar Cambios</button>
        </form>
    </main>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Cambios del Grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Nombre del Grupo:</strong> <span id="confirmNombre"></span></p>
                    <p><strong>Descripción:</strong> <span id="confirmDescripcion"></span></p>
                    <p><strong>Privilegios:</strong> <span id="confirmPrivilegios"></span></p>
                    <p><strong>PaaS Seleccionados:</strong> <span id="confirmPaaS"></span></p>
                    <p><strong>SaaS Seleccionados:</strong> <span id="confirmSaaS"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="finalSubmit">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('editGroupForm');
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

            document.getElementById('confirmChanges').addEventListener('click', () => {
                const selectedPaaSSet = new Set();

                // Añadir PaaS seleccionados directamente
                document.querySelectorAll('.paas-check:checked').forEach(opt => {
                    selectedPaaSSet.add(opt.nextElementSibling.textContent.trim());
                });

                // Añadir PaaS vinculados a SaaS seleccionados
                document.querySelectorAll('.saas-check:checked').forEach(opt => {
                    const paasName = opt.getAttribute('data-paas-name');
                    if (paasName) {
                        selectedPaaSSet.add(paasName);
                    }
                });

                // Mostrar datos en el modal
                document.getElementById('confirmNombre').textContent = document.getElementById('nombre').value;
                document.getElementById('confirmDescripcion').textContent = document.getElementById('descripcion').value || 'Sin descripción';

                const selectedPrivileges = Array.from(document.querySelectorAll('[name="privilegios[]"]:checked'))
                    .map(priv => priv.nextElementSibling.textContent.trim());
                document.getElementById('confirmPrivilegios').textContent = selectedPrivileges.join(', ') || 'Sin privilegios';

                document.getElementById('confirmPaaS').textContent = Array.from(selectedPaaSSet).join(', ') || 'Sin PaaS';

                const selectedSaaS = Array.from(document.querySelectorAll('.saas-check:checked')).map(opt => opt.nextElementSibling.textContent.trim());
                document.getElementById('confirmSaaS').textContent = selectedSaaS.join(', ') || 'Sin SaaS';

                confirmModal.show();
            });

            // Enviar formulario al confirmar
            document.getElementById('finalSubmit').addEventListener('click', () => {
                form.submit();
            });
        });

        </script>
</body>
</html>
