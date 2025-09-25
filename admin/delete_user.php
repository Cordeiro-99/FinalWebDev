<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("ID de utilizador não fornecido.");
}

$id = intval($_GET['id']);

$conn->query("DELETE FROM users WHERE id = $id");

header("Location: admin.php");
exit();
