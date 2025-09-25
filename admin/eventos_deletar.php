<?php
session_start();
include '../config.php';

// Verifica se o utilizador tem permissão para excluir 
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acesso negado.");
}

// Verifica se o ID foi enviado via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de evento inválido.");
}

$id = intval($_GET['id']);

// Verifica se o evento existe
$stmt_check = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    die("Evento não encontrado.");
}
$stmt_check->close();

// Executa a exclusão
$stmt_delete = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    header("Location: admin.php");
    exit;
} else {
    echo "Erro ao deletar o evento.";
}

$stmt_delete->close();
$conn->close();
?>
