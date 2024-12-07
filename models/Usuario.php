<?php

class Usuario
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function findByEmail($email)
    {
        $query = "SELECT * FROM usuario WHERE Email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createUser($data)
    {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $query = "INSERT INTO usuario (Nombre, Usuario, Email, Telefono, Contraseña, Direccion, FechaRegistro) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssssss',
            $data['name'],
            $data['username'],
            $data['email'],
            $data['phone'],
            $hashed_password,
            $data['address']
        );
        return $stmt->execute();
    }
}
?>
