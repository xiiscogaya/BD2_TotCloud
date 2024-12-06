<?php
// controllers/AuthController.php

class AuthController {
    private $usuarioModel;

    public function __construct($pdo) {
        require_once __DIR__ . '/../models/Usuario.php';
        $this->usuarioModel = new Usuario($pdo);
    }

    public function loginUser($email, $password) {
        $user = $this->usuarioModel->findByEmail($email);
        if ($user && password_verify($password, $user['contrasena'])) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_name'] = $user['nombre'];
            return true;
        }
        return false;
    }

    public function registerUser($data) {
        // Podrías añadir más validaciones
        if (empty($data['nombre']) || empty($data['email']) || empty($data['telefono']) || 
            empty($data['direccion']) || empty($data['contrasena'])) {
            return false;
        }

        // Comprobar si el email ya existe
        if ($this->usuarioModel->findByEmail($data['email'])) {
            return false; // Ya existe
        }

        return $this->usuarioModel->createUser($data);
    }

    public function logoutUser() {
        session_unset();
        session_destroy();
    }
}
