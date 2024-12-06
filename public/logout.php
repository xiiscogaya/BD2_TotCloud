<?php
// public/logout.php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController($pdo);
$auth->logoutUser();

redirect('index.php');
