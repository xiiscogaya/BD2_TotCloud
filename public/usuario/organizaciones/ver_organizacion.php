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
    SELECT s.*
    FROM saas s
    JOIN r_saas_grup rsg ON s.idSaaS = rsg.idSaaS
    JOIN grupo g ON rsg.idGrup = g.idGrupo
    WHERE g.idOrg = ?";
$stmt_saas = $conn->prepare($query_saas);
$stmt_saas->bind_param('i', $idOrganizacion);
$stmt_saas->execute();
$result_saas = $stmt_saas->get_result();

// Obtener lista de PaaS asociados
$query_paas = "
    SELECT p.* 
    FROM paas p 
    JOIN r_paas_grup rpg ON p.idPaaS = rpg.idPaaS
    JOIN grupo g ON rpg.idGrup = g.idGrupo
    WHERE g.idOrg = ?";
$stmt_paas = $conn->prepare($query_paas);
$stmt_paas->bind_param('i', $idOrganizacion);
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

    <!-- Contenido principal -->
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

        <!-- Acciones generales -->
        <div class="d-flex justify-content-between mb-4">
            <?php if (in_array('Gestionar grupos', $privileges)): ?>
                <a href="gestionar_grupos/gestionar_grupos.php?idOrg=<?php echo $idOrganizacion; ?>" class="btn btn-info">Gestionar Grupos</a>
            <?php endif; ?>
            <?php if (in_array('Añadir usuarios', $privileges)): ?>
                <a href="añadir_usuarios/añadir_usuarios.php?idOrg=<?php echo $idOrganizacion; ?>" class="btn btn-warning">Añadir Personas</a>
            <?php endif; ?>
        </div>

        <!-- SaaS -->
        <h2 class="text-center mb-4">Lista de SaaS Asociados</h2>
        <?php if (in_array('Contratar saas', $privileges)): ?>
        <div class="mb-3 text-end">
            <a href="contratar_saas/contratar_saas.php?idOrg=<?php echo $idOrganizacion; ?>" class="btn btn-success">Contratar Nuevo SaaS</a>
        </div>
        <?php endif; ?>
        <?php if ($result_saas->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Contraseña</th>
                    <?php if (in_array('Modificar saas', $privileges)): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($saas = $result_saas->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($saas['idSaaS']); ?></td>
                        <td><?php echo htmlspecialchars($saas['Nombre']); ?></td>
                        <td><?php echo htmlspecialchars($saas['Usuario']); ?></td>
                        <td><?php echo htmlspecialchars($saas['Contraseña']); ?></td>
                        <?php if (in_array('Modificar saas', $privileges)): ?>
                        <td>
                            <a href= "modificar_saas.php?id=<?php echo $saas['idSaaS']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No hay SaaS asociados a esta organización.</p>
        <?php endif; ?>

        <!-- PaaS -->
        <h2 class="text-center mb-4">Lista de PaaS Asociados</h2>
        <?php if (in_array('Contratar paas', $privileges)): ?>
        <div class="mb-3 text-end">
            <a href="contratar_paas/contratar_paas.php?idOrg=<?php echo $idOrganizacion; ?>" class="btn btn-success">Contratar Nuevo PaaS</a>
        </div>
        <?php endif; ?>
        <?php if ($result_paas->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <?php if (in_array('Modificar paas', $privileges)): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($paas = $result_paas->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($paas['idPaaS']); ?></td>
                        <td><?php echo htmlspecialchars($paas['Nombre']); ?></td>
                        <td><?php echo htmlspecialchars($paas['Estado']); ?></td>
                        <?php if (in_array('Modificar paas', $privileges)): ?>
                        <td>
                            <a href="modificar_paas.php?id=<?php echo $paas['idPaaS']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No hay PaaS asociados a esta organización.</p>
        <?php endif; ?>
    </main>

    <!-- Pie de página -->
    <?php include '../../../includes/footer.php'; ?>
</body>
</html>
