<?php
include '../../../includes/db_connect.php';

$idPaaS = intval($_GET['id'] ?? 0);
$data = [];

if ($idPaaS > 0) {
    // Obtener CPU, RAM y almacenamiento asociados
    $tables = [
        'CPU' => ['table' => 'cpu', 'column' => 'idCPU'],
        'RAM' => ['table' => 'ram', 'column' => 'idRAM'],
        'Almacenamiento' => ['table' => 'almacenamiento', 'column' => 'idAlmacenamiento']
    ];

    foreach ($tables as $type => $info) {
        $query = "SELECT {$info['table']}.Nombre AS nombre, r.Cantidad AS cantidad 
                  FROM {$info['table']} 
                  INNER JOIN r_paas_{$info['table']} r ON {$info['table']}.{$info['column']} = r.{$info['column']} 
                  WHERE r.idPaaS = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $idPaaS);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $data[] = ['tipo' => $type, 'nombre' => $row['nombre'], 'cantidad' => $row['cantidad']];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($data);
