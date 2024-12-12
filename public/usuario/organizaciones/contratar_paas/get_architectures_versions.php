<?php
include '../../../../includes/db_connect.php';

$idSO = intval($_GET['idSO'] ?? 0);
$response = ['architectures' => [], 'versions' => []];

if ($idSO > 0) {
    // Obtener arquitecturas y versiones del sistema operativo
    $query = "SELECT DISTINCT Arquitectura, Version FROM sistemaoperativo WHERE idSO = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $idSO);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['architectures'][] = $row['Arquitectura'];
        $response['versions'][] = $row['Version'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
