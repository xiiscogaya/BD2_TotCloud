<?php
include '../../../includes/db_connect.php';

if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
    $nombre = $_GET['nombre'];

    $query = "SELECT idMotor, Version FROM motor WHERE Nombre = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $nombre);
    $stmt->execute();
    $result = $stmt->get_result();

    $versions = [];
    while ($row = $result->fetch_assoc()) {
        $versions[] = ['idMotor' => $row['idMotor'], 'version' => $row['Version']];
    }

    header('Content-Type: application/json');
    echo json_encode(['versions' => $versions]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Nombre de motor no proporcionado']);
exit;
