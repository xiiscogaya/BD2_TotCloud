<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de organización
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../usuario.php');
    exit;
}

$idOrganizacion = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Verificar que el usuario tiene acceso a esta organización
$query_check = "SELECT * FROM r_usuario_org WHERE idUsuario = ? AND idOrg = ?";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param('ii', $user_id, $idOrganizacion);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    $_SESSION['error_message'] = 'No tienes acceso a esta organización.';
    header('Location: ../usuario.php');
    exit;
}

// Obtener datos de la organización
$query_org = "SELECT * FROM organizacion WHERE idOrganizacion = ?";
$stmt_org = $conn->prepare($query_org);
$stmt_org->bind_param('i', $idOrganizacion);
$stmt_org->execute();
$org = $stmt_org->get_result()->fetch_assoc();

// Obtener los privilegios del usuario en esta organización
$query_privileges = "
    SELECT DISTINCT p.Nombre 
    FROM privilegio p
    JOIN r_grup_priv rgp ON p.idPrivilegio = rgp.idPriv
    JOIN grupo g ON rgp.idGrup = g.idGrupo
    JOIN r_usuario_grupo rug ON rug.idGrupo = g.idGrupo
    WHERE rug.idUsuario = ? AND g.idOrg = ?";
$stmt_privileges = $conn->prepare($query_privileges);
$stmt_privileges->bind_param('ii', $user_id, $idOrganizacion);
$stmt_privileges->execute();
$result_privileges = $stmt_privileges->get_result();

$privileges = [];
while ($privilege = $result_privileges->fetch_assoc()) {
    $privileges[] = $privilege['Nombre'];
}

// Obtener lista de SaaS asociados
$query_saas = "
    SELECT DISTINCT s.*
    FROM saas s
    JOIN r_saas_grup rsg ON s.idSaaS = rsg.idSaaS
    JOIN grupo g ON rsg.idGrup = g.idGrupo
    JOIN r_usuario_grupo rug ON rug.idGrupo = g.idGrupo
    WHERE g.idOrg = ? AND rug.idUsuario = ?";
$stmt_saas = $conn->prepare($query_saas);
$stmt_saas->bind_param('ii', $idOrganizacion, $user_id);
$stmt_saas->execute();
$result_saas = $stmt_saas->get_result();


// Obtener lista de PaaS asociados
$query_paas = "
    SELECT DISTINCT p.* 
    FROM paas p
    JOIN r_paas_grup rpg ON p.idPaaS = rpg.idPaaS
    JOIN grupo g ON rpg.idGrup = g.idGrupo
    JOIN r_usuario_grupo rug ON rug.idGrupo = g.idGrupo
    WHERE g.idOrg = ? AND rug.idUsuario = ?";
$stmt_paas = $conn->prepare($query_paas);
$stmt_paas->bind_param('ii', $idOrganizacion, $user_id);
$stmt_paas->execute();
$result_paas = $stmt_paas->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organización: <?php echo htmlspecialchars($org['Nombre']); ?> - TotCloud</title>
    <link href="../../css/estilos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Organización: <?php echo htmlspecialchars($org['Nombre']); ?></h1>
            <p><?php echo htmlspecialchars($org['Descripcion']); ?></p>
        </div>
        <div>
            <a href="../usuario.php" class="btn btn-outline-light">Volver a Mis Organizaciones</a>
        </div>
    </header>

    <main class="container my-5">
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']);
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message']);
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Acciones generales -->
        <div class="d-flex justify-content-between mb-4">
            <?php if (in_array('Gestionar grupos', $privileges)): ?>
                <a href="gestionar_grupos/gestionar_grupos.php?idOrg=<?php echo $idOrganizacion; ?>"
                    class="btn btn-info">Gestionar Grupos</a>
            <?php endif; ?>
            <?php if (in_array('Añadir usuarios', $privileges)): ?>
                <a href="añadir_usuarios/añadir_usuarios.php?idOrg=<?php echo $idOrganizacion; ?>"
                    class="btn btn-warning">Añadir Personas</a>
            <?php endif; ?>
        </div>

        <!-- Lista de PaaS -->
        <h2 class="text-center mb-4">Lista de PaaS Asociados</h2>
        <?php if (in_array('Contratar paas', $privileges)): ?>
            <div class="mb-3 text-end">
                <a href="contratar_paas/contratar_paas.php?idOrg=<?php echo $idOrganizacion; ?>"
                    class="btn btn-success">Contratar Nuevo PaaS</a>
            </div>
        <?php endif; ?>
        <?php if ($result_paas->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($paas = $result_paas->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a href="#" class="paas-link text-primary" data-id="<?php echo $paas['idPaaS']; ?>"
                                    data-bs-toggle="modal" data-bs-target="#detailsModal">
                                    <?php echo htmlspecialchars($paas['Nombre']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($paas['Estado']); ?></td>
                            <td>
                                <?php if (in_array('Modificar paas', $privileges)): ?>
                                    <a href="modificar_paas.php?id=<?php echo $paas['idPaaS']; ?>"
                                        class="btn btn-warning btn-sm">Editar</a>
                                <?php endif; ?>
                                <?php if (in_array('Eliminar paas', $privileges)): ?>
                                    <a href="eliminar_paas.php?id=<?php echo $paas['idPaaS']; ?>"
                                        class="btn btn-danger btn-sm">Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay PaaS asociados a esta organización.</p>
        <?php endif; ?>


        <!-- Modal Para PaaS -->
        <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailsModalLabel">Detalles</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6><strong>Nombre:</strong> <span id="modalNombre"></span></h6>
                        <h6><strong>Estado/Usuario:</strong> <span id="modalExtra"></span></h6>
                        <hr>
                        <h6><strong>Componentes:</strong></h6>
                        <ul id="modalComponentes"></ul>
                        <h6><strong>Coste Total:</strong> <span id="modalCoste"></span></h6>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de SaaS -->
        <h2 class="text-center mb-4">Lista de SaaS Asociados</h2>
        <?php if (in_array('Contratar saas', $privileges)): ?>
            <div class="mb-3 text-end">
                <a href="contratar_saas/contratar_saas.php?idOrg=<?php echo $idOrganizacion; ?>"
                    class="btn btn-success">Contratar Nuevo SaaS</a>
            </div>
        <?php endif; ?>
        <?php if ($result_saas->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($saas = $result_saas->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a href="#" class="saas-link text-primary" data-id="<?php echo $saas['idSaaS']; ?>"
                                    data-bs-toggle="modal" data-bs-target="#saasModal">
                                    <?php echo htmlspecialchars($saas['Nombre']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($saas['Usuario']); ?></td>
                            <td>
                                <?php if (in_array('Modificar saas', $privileges)): ?>
                                    <a href="modificar_saas.php?id=<?php echo $saas['idSaaS']; ?>"
                                        class="btn btn-warning btn-sm">Editar</a>
                                <?php endif; ?>
                                <?php if (in_array('Eliminar saas', $privileges)): ?>
                                    <a href="eliminar_saas.php?id=<?php echo $saas['idSaaS']; ?>"
                                        class="btn btn-danger btn-sm">Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay SaaS asociados a esta organización.</p>
        <?php endif; ?>

        <!-- Modal para SaaS -->
        <div class="modal fade" id="saasModal" tabindex="-1" aria-labelledby="saasModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="saasModalLabel">Detalles de SaaS</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6><strong>Nombre:</strong> <span id="modalSaasNombre"></span></h6>
                        <h6><strong>Usuario:</strong> <span id="modalSaasUsuario"></span></h6>
                        <h6><strong>Contraseña:</strong> <span id="modalSaasContraseña"></span></h6>
                        <h6><strong>Motor:</strong> <span id="modalSaasMotor"></span></h6>
                        <h6><strong>Versión:</strong> <span id="modalSaasVersion"></span></h6>
                        <hr>
                        <h6><strong>Componentes del PaaS Asociado:</strong></h6>
                        <ul id="modalSaasComponentes"></ul>
                        <h6><strong>Coste Total:</strong> <span id="modalSaasCoste"></span></h6>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const detailsModal = document.getElementById('detailsModal');
            const saasModal = document.getElementById('saasModal');

            // Modal para PaaS
            detailsModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');

                fetch(`fetch_paas_details.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data || !data.details) {
                            document.getElementById('modalNombre').textContent = 'Sin datos';
                            document.getElementById('modalExtra').textContent = 'N/A';
                            document.getElementById('modalCoste').textContent = '0 €';
                            document.getElementById('modalComponentes').innerHTML = '<li>Sin datos</li>';
                            return;
                        }

                        document.getElementById('modalNombre').textContent = data.details.Nombre || 'Sin nombre';
                        document.getElementById('modalExtra').textContent = data.details.Estado || 'Desconocido';
                        document.getElementById('modalCoste').textContent = (data.details.CosteTotal || 0) + ' €';

                        const componentesList = document.getElementById('modalComponentes');
                        componentesList.innerHTML = '';
                        if (data.components && data.components.length > 0) {
                            data.components.forEach(component => {
                                const li = document.createElement('li');
                                li.textContent = `${component.Tipo}: ${component.Nombre} (Cantidad: ${component.Cantidad})`;
                                componentesList.appendChild(li);
                            });
                        } else {
                            componentesList.innerHTML = '<li>Sin componentes</li>';
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar detalles:', error);
                        document.getElementById('modalNombre').textContent = 'Error';
                        document.getElementById('modalExtra').textContent = 'Error';
                        document.getElementById('modalCoste').textContent = '0 €';
                        document.getElementById('modalComponentes').innerHTML = '<li>Error al cargar componentes</li>';
                    });
            });

            // Modal para SaaS
            saasModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');

                fetch(`fetch_saas_details.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data || !data.details) {
                            document.getElementById('modalSaasNombre').textContent = 'Sin datos';
                            document.getElementById('modalSaasUsuario').textContent = 'N/A';
                            document.getElementById('modalSaasContraseña').textContent = 'N/A';
                            document.getElementById('modalSaasMotor').textContent = 'N/A';
                            document.getElementById('modalSaasVersion').textContent = 'N/A';
                            document.getElementById('modalSaasCoste').textContent = '0 €';
                            document.getElementById('modalSaasComponentes').innerHTML = '<li>Sin datos</li>';
                            return;
                        }

                        document.getElementById('modalSaasNombre').textContent = data.details.Nombre || 'Sin nombre';
                        document.getElementById('modalSaasUsuario').textContent = data.details.Usuario || 'Desconocido';
                        document.getElementById('modalSaasContraseña').textContent = data.details.Contraseña || 'Desconocida';
                        document.getElementById('modalSaasMotor').textContent = data.details.Motor || 'N/A';
                        document.getElementById('modalSaasVersion').textContent = data.details.Version || 'N/A';
                        document.getElementById('modalSaasCoste').textContent = (data.details.CosteTotal || 0) + ' €';

                        const componentesList = document.getElementById('modalSaasComponentes');
                        componentesList.innerHTML = '';
                        if (data.components && data.components.length > 0) {
                            data.components.forEach(component => {
                                const li = document.createElement('li');
                                li.textContent = `${component.Tipo}: ${component.Nombre} (Cantidad: ${component.Cantidad})`;
                                componentesList.appendChild(li);
                            });
                        } else {
                            componentesList.innerHTML = '<li>Sin componentes</li>';
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar detalles:', error);
                        document.getElementById('modalSaasNombre').textContent = 'Error';
                        document.getElementById('modalSaasUsuario').textContent = 'Error';
                        document.getElementById('modalSaasContraseña').textContent = 'Error';
                        document.getElementById('modalSaasMotor').textContent = 'Error';
                        document.getElementById('modalSaasVersion').textContent = 'Error';
                        document.getElementById('modalSaasCoste').textContent = '0 €';
                        document.getElementById('modalSaasComponentes').innerHTML = '<li>Error al cargar componentes</li>';
                    });
            });
        });
    </script>

</body>
</html>