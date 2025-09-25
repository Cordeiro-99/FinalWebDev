<?php
session_start();
 
// Verifica se o ID do produto foi enviado
if (isset($_POST['id'])) {
    $productId = $_POST['id'];
    $productName = $_POST['name'];
    $productPrice = $_POST['price'];
 
    // Verifica se a quantidade foi enviada, caso contrário, define como 1
    $productQuantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
 
    // Inicializa o carrinho se não existir
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
 
    // Adiciona o produto ao carrinho
    if (isset($_SESSION['cart'][$productId])) {
        // Se o produto já estiver no carrinho, atualiza a quantidade
        $_SESSION['cart'][$productId]['quantity'] += $productQuantity;
    } else {
        // Se o produto não estiver no carrinho, adiciona-o
        $_SESSION['cart'][$productId] = [
            'name' => $productName,
            'price' => $productPrice,
            'quantity' => $productQuantity
        ];
    }
 
    // Redireciona de volta para a página do catálogo
    header("Location: index.php");
    exit();
} else {
    die("Produto não encontrado.");
}
