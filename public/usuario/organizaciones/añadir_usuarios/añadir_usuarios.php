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
            <h1 class="h3 mb-0">Añadir Usuarios a Organización</h1>
        </div>
        <div>
            <a href="../ver_organizacion.php?id=<?php echo $idOrganizacion; ?>" class="btn btn-outline-light">Volver a Organización</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Buscar Usuarios</h2>

        <div class="mb-3">
            <label for="search" class="form-label">Buscar por Nombre</label>
            <input type="text" class="form-control" id="search" placeholder="Escribe el nombre del usuario">
        </div>

        <div id="results" class="mt-4">
            <!-- Aquí se mostrarán los resultados de búsqueda -->
        </div>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>

    <script>
        $(document).ready(function () {
            $('#search').on('input', function () {
                const query = $(this).val();
                if (query.length > 2) { // Iniciar búsqueda después de 3 caracteres
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
        });

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
    </script>
</body>
</html>
