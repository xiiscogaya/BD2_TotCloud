<?php
// models/Usuario.php

class Usuario {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function createUser($data) {
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, email, telefono, direccion, contrasena, fecha_registro) 
            VALUES (:nombre, :email, :telefono, :direccion, :contrasena, NOW())");
        return $stmt->execute([
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'telefono' => $data['telefono'],
            'direccion' => $data['direccion'],
            'contrasena' => password_hash($data['contrasena'], PASSWORD_DEFAULT)
        ]);
    }
}
