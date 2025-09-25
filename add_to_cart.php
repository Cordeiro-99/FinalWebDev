<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $event_id = (int) $_POST['event_id'];
    $quantidade = (int) $_POST['quantidade'];

    if ($quantidade < 1) {
        header("Location: index.php");
        exit();
    }

    // Verificar stock atual
    $stmt = $conn->prepare("SELECT stock FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($stock);
    $stmt->fetch();
    $stmt->close();

    if ($quantidade > $stock) {
        header("Location: index.php?erro=stock");
        exit();
    }

    // Verificar se já existe no carrinho
    $stmt = $conn->prepare("SELECT quantidade FROM cart WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Atualiza quantidade
        $stmt->bind_result($qtd_atual);
        $stmt->fetch();
        $stmt->close();

        $nova_qtd = $qtd_atual + $quantidade;
        if ($nova_qtd > $stock) {
            $nova_qtd = $stock;
        }

        $update = $conn->prepare("UPDATE cart SET quantidade = ? WHERE user_id = ? AND event_id = ?");
        $update->bind_param("iii", $nova_qtd, $user_id, $event_id);
        $update->execute();
        $update->close();
    } else {
        // Inserir novo
        $stmt->close();
        $insert = $conn->prepare("INSERT INTO cart (user_id, event_id, quantidade) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $user_id, $event_id, $quantidade);
        $insert->execute();
        $insert->close();
    }

    header("Location: cart.php");
    exit();
}
?>
