<?php
session_start();

// Verifica se há um ID para remover
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Garante que o carrinho existe na sessão
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]); // Remove o item
    }
}

// Direciona de volta para o carrinho
header("Location: cart.php");
exit();
?>
