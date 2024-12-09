<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Mostrar mensaje de éxito al crear paas
$success_message_crear = '';
if (isset($_SESSION['success_message_crear'])) {
    $success_message = $_SESSION['success_message_crear'];
    unset($_SESSION['success_message_crear']); // Eliminar mensaje para evitar que se muestre de nuevo
}

// Mostrar mensaje de éxito al editar paas si existe
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Eliminar mensaje para evitar que se muestre de nuevo
}
// Mostrar mensaje de éxito al eliminar el paas si existe
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Eliminar el mensaje después de mostrarlo
}



// Obtener todas las configuraciones PaaS
$query = "SELECT * FROM paas";
$result = $conn->query($query);

// Manejar cambios de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPaaS'], $_POST['estado'])) {
    $idPaaS = intval($_POST['idPaaS']);
    $estado = $_POST['estado'] === 'Activo' ? 'Activo' : 'En pruebas';

    $update_query = "UPDATE paas SET Estado = ? WHERE idPaaS = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('si', $estado, $idPaaS);
    $stmt->execute();

    // Recargar la página para reflejar los cambios
    header("Location: modificar_paas.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Lista PaaS - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../css/estilos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .slider-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .slider-label {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white text-center py-3">
        <h1>Modificar Lista PaaS</h1>
    </header>

    <!-- Botón de Volver a Trabajador -->
    <div class="container my-3">
        <a href="../trabajador.php" class="btn btn-secondary">Volver</a>
    </div>

    <!-- Mostrar mensaje de exito al crear paas -->
    <?php if (!empty($success_message_crear)): ?>
        <div class="alert alert-success text-center">
            <?php echo htmlspecialchars($success_message_crear); ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar mensaje de cambios realizados -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success text-center">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar mensaje de éxito al eliminar paas -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger text-center">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Contenido principal -->
    <main class="container my-5">
        <!-- Botón para crear nueva configuración PaaS -->
        <div class="text-end mb-3">
            <a href="crear_paas.php" class="btn btn-success">Crear nueva configuración PaaS</a>
        </div>

        <!-- Lista de configuraciones PaaS -->
        <h2 class="mb-4">Configuraciones PaaS existentes</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Sistema Operativo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['idPaaS']); ?></td>
                            <td>
                                <!-- Botón para abrir el modal -->
                                <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#detailsModal" 
                                    data-id="<?php echo $row['idPaaS']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($row['Nombre']); ?>"
                                    data-estado="<?php echo htmlspecialchars($row['Estado']); ?>"
                                    data-sistema="<?php echo htmlspecialchars($row['idSO']); ?>">
                                    <?php echo htmlspecialchars($row['Nombre']); ?>
                                </a>
                            </td>
                            <td>
                                <!-- Slider para cambiar estado -->
                                <form method="POST" action="modificar_paas.php" class="slider-container">
                                    <input type="hidden" name="idPaaS" value="<?php echo $row['idPaaS']; ?>">
                                    <!-- Campo oculto para estado "En pruebas" -->
                                    <input type="hidden" name="estado" value="En pruebas">
                                    <span class="slider-label">En pruebas</span>
                                    <label class="form-check form-switch">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            name="estado" 
                                            value="Activo" 
                                            onchange="this.form.submit()"
                                            <?php echo $row['Estado'] === 'Activo' ? 'checked' : ''; ?>
                                        >
                                    </label>
                                    <span class="slider-label">Activo</span>
                                </form>
                            </td>
                            <td><?php echo htmlspecialchars($row['idSO']); ?></td>
                            <td>
                                <a href="editar_paas.php?id=<?php echo $row['idPaaS']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                <a href="eliminar_paas.php?id=<?php echo $row['idPaaS']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta configuración PaaS?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No hay configuraciones PaaS registradas.</p>
        <?php endif; ?>
    </main>

    <!-- Modal para mostrar detalles -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Detalles de Configuración PaaS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6><strong>Nombre:</strong> <span id="modalNombre"></span></h6>
                    <h6><strong>Estado:</strong> <span id="modalEstado"></span></h6>
                    <h6><strong>Sistema Operativo:</strong> <span id="modalSistema"></span></h6>
                    <hr>
                    <h6><strong>Componentes:</strong></h6>
                    <ul id="modalComponentes"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <?php include '../../../includes/footer.php'; ?>

    <script>
        const detailsModal = document.getElementById('detailsModal');
        detailsModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const idPaaS = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            const estado = button.getAttribute('data-estado');
            const sistema = button.getAttribute('data-sistema');

            // Actualizar datos básicos en el modal
            document.getElementById('modalNombre').textContent = nombre;
            document.getElementById('modalEstado').textContent = estado;
            document.getElementById('modalSistema').textContent = sistema;

            // Fetch de componentes asociados
            fetch(`get_paas_details.php?id=${idPaaS}`)
                .then(response => response.json())
                .then(data => {
                    const componentesList = document.getElementById('modalComponentes');
                    componentesList.innerHTML = '';
                    data.forEach(component => {
                        const li = document.createElement('li');
                        li.textContent = `${component.tipo}: ${component.nombre} (Cantidad: ${component.cantidad})`;
                        componentesList.appendChild(li);
                    });
                })
                .catch(error => console.error('Error al cargar los detalles:', error));
        });
    </script>
</body>
</html>
