<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de organización
if (!isset($_GET['idOrg']) || empty($_GET['idOrg'])) {
    header('Location: ../../usuario.php');
    exit;
}

$idOrganizacion = intval($_GET['idOrg']);

// Verificar si el usuario tiene acceso a esta organización
$query_check = "SELECT * FROM r_usuario_org WHERE idUsuario = ? AND idOrg = ?";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param('ii', $_SESSION['user_id'], $idOrganizacion);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    $_SESSION['error_message'] = 'No tienes acceso a esta organización.';
    header('Location: ../../usuario.php');
    exit;
}

// Obtener usuarios que ya están en la organización
$query_users_in_org = "
    SELECT u.idUsuario, u.Nombre, u.Email 
    FROM usuario u
    JOIN r_usuario_org ruo ON u.idUsuario = ruo.idUsuario
    WHERE ruo.idOrg = ?";
$stmt_users_in_org = $conn->prepare($query_users_in_org);
$stmt_users_in_org->bind_param('i', $idOrganizacion);
$stmt_users_in_org->execute();
$result_users_in_org = $stmt_users_in_org->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Usuarios a Organización - TotCloud</title>
    <link href="../../../css/estilos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Gestión de Usuarios de la Organización</h1>
        </div>
        <div>
            <a href="../ver_organizacion.php?id=<?php echo $idOrganizacion; ?>" class="btn btn-outline-light">Volver a Organización</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <!-- Sección para buscar y añadir usuarios -->
        <h2 class="text-center mb-4">Buscar Usuarios para Añadir</h2>
        <div class="mb-3">
            <label for="search" class="form-label">Buscar por Nombre</label>
            <input type="text" class="form-control" id="search" placeholder="Escribe el nombre del usuario">
        </div>

        <div id="results" class="mt-4">
            <!-- Aquí se mostrarán los resultados de búsqueda -->
        </div>

        <!-- Sección para listar usuarios en la organización -->
        <h2 class="text-center my-5">Usuarios en la Organización</h2>
        <form id="updateUsersForm">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Seleccionar</th>
                        <th>Nombre</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result_users_in_org->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="usuarios[]" value="<?php echo $user['idUsuario']; ?>" checked>
                            </td>
                            <td><?php echo htmlspecialchars($user['Nombre']); ?></td>
                            <td><?php echo htmlspecialchars($user['Email']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-danger">Actualizar Usuarios</button>
        </form>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>

    <script>
        // Definir la función addUser() en el ámbito global
        function addUser(userId) {
            const orgId = <?php echo $idOrganizacion; ?>;
            $.ajax({
                url: 'agregar_usuario.php',
                method: 'POST',
                data: { userId: userId, idOrg: orgId },
                success: function (response) {
                    alert(response);
                    $('#search').trigger('input'); // Actualizar resultados después de añadir
                }
            });
        }

        $(document).ready(function () {
            // Buscar usuarios
            $('#search').on('input', function () {
                const query = $(this).val();
                if (query.length > 2) {
                    $.ajax({
                        url: 'buscar_usuario.php',
                        method: 'POST',
                        data: { search: query, idOrg: <?php echo $idOrganizacion; ?> },
                        success: function (data) {
                            $('#results').html(data);
                        }
                    });
                } else {
                    $('#results').html('');
                }
            });

            // Actualizar usuarios en la organización
            $('#updateUsersForm').on('submit', function (e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.ajax({
                    url: 'actualizar_usuarios_org.php',
                    method: 'POST',
                    data: formData + '&idOrg=<?php echo $idOrganizacion; ?>',
                    success: function (response) {
                        alert(response);
                        location.reload(); // Recargar la página después de actualizar
                    }
                });
            });
        });
    </script>
</body>
</html>
