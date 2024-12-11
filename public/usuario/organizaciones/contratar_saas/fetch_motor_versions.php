<?php
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
    $nombre = $_GET['nombre'];

    $query_versions = "SELECT idMotor, Version FROM motor WHERE Nombre = ?";
    $stmt_versions = $conn->prepare($query_versions);
    $stmt_versions->bind_param('s', $nombre);
    $stmt_versions->execute();
    $result_versions = $stmt_versions->get_result();

    $versions = [];
    while ($version = $result_versions->fetch_assoc()) {
        $versions[] = $version;
    }

    echo json_encode($versions);
} else {
    echo json_encode([]);
}
?>
