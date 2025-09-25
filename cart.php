<?php
session_start();
include 'config.php';

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Atualizar quantidade individualmente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_individual'])) {
    $cart_id = (int) $_POST['cart_id'];
    $nova_quantidade = (int) $_POST['nova_quantidade'];

    if ($nova_quantidade > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantidade = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $nova_quantidade, $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: cart.php");
    exit();
}

// Ação de remover item do carrinho
if (isset($_GET['remover']) && is_numeric($_GET['remover'])) {
    $item_id = (int) $_GET['remover'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $item_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: cart.php");
    exit();
}

// Buscar itens do carrinho com validação de stock
$stmt = $conn->prepare("
    SELECT cart.id AS cart_id, events.titulo, events.preco, events.imagem, cart.quantidade, events.stock
    FROM cart
    JOIN events ON cart.event_id = events.id
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$itens_invalidos = false;
$items = [];

while ($item = $result->fetch_assoc()) {
    if ($item['quantidade'] > $item['stock']) {
        $itens_invalidos = true;
    }
    $items[] = $item;
    $total += $item['preco'] * $item['quantidade'];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Carrinho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/cart.css">
</head>

<body>
    <div class="container my-5">
        <h1 class="mb-4">Carrinho de Compras</h1>

        <?php if (!empty($items)): ?>
            <?php if ($itens_invalidos): ?>
                <div class="alert alert-danger">
                    Um ou mais itens no carrinho excedem o stock disponível.
                </div>
            <?php endif; ?>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Imagem</th>
                        <th>Quantidade</th>
                        <th>Stock</th>
                        <th>Preço</th>
                        <th>Total</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php $subtotal = $item['preco'] * $item['quantidade']; ?>
                        <tr class="<?= $item['quantidade'] > $item['stock'] ? 'table-danger' : '' ?>">
                            <td><?= htmlspecialchars($item['titulo']) ?></td>
                            <td>
                                <?php if (!empty($item['imagem'])): ?>
                                    <img src="<?= htmlspecialchars($item['imagem']) ?>" alt="Evento" width="100">
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" action="cart.php" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <input type="number" name="nova_quantidade" value="<?= $item['quantidade'] ?>" min="1"
                                        class="form-control form-control-sm" style="width: 80px;">
                                    <button type="submit" name="atualizar_individual"
                                        class="btn btn-sm btn-primary">Atualizar</button>
                                </form>
                            </td>
                            <td><?= $item['stock'] ?></td>

                            <td>€<?= number_format($item['preco'], 2, ',', '.') ?></td>
                            <td>€<?= number_format($subtotal, 2, ',', '.') ?></td>
                            <td>
                                <a href="cart.php?remover=<?= $item['cart_id'] ?>" class="btn btn-danger btn-sm">Remover</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h4 class="text-end">Total: €<?= number_format($total, 2, ',', '.') ?></h4>
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">← Voltar para a Loja</a>
                <a href="checkout.php" class="btn btn-success<?= $itens_invalidos ? ' disabled' : '' ?>">Finalizar
                    Compra</a>
            </div>

        <?php else: ?>
            <p class="text-muted">O seu carrinho está vazio.</p>
            <a href="index.php" class="btn btn-primary">← Voltar para a loja</a>
        <?php endif; ?>
    </div>
</body>

</html>

<?php $conn->close(); ?>
