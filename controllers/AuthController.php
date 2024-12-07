<?php
include_once '../includes/db_connect.php';

class AuthController
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function login($username, $password)
    {
        $query = "SELECT * FROM usuario WHERE Usuario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Contraseña'])) {
                $_SESSION['user_id'] = $user['idUsuario'];
                $_SESSION['username'] = $user['Usuario'];
                return true;
            }
        }

        return false;
    }

    public function register($username, $email, $password)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO usuario (Usuario, Email, Contraseña, FechaRegistro) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sss', $username, $email, $hashed_password);

        return $stmt->execute();
    }
}
?>
