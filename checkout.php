<?php
session_start();
include 'config.php';

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Buscar itens do carrinho, incluindo stock atual
$stmt = $conn->prepare("
    SELECT cart.event_id, cart.quantidade, events.preco, events.stock
    FROM cart
    JOIN events ON cart.event_id = events.id
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;
$valid = true;

while ($row = $result->fetch_assoc()) {
    if ($row['quantidade'] > $row['stock']) {
        $valid = false;
    }
    $items[] = $row;
    $total += $row['preco'] * $row['quantidade'];
}
$stmt->close();

if (empty($items)) {
    header("Location: cart.php");
    exit();
}

// Processar compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $conn->begin_transaction();
    $now = date('Y-m-d H:i:s');
    $erro_stock = false;

    foreach ($items as $item) {
        $stmt = $conn->prepare("SELECT stock FROM events WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $item['event_id']);
        $stmt->execute();
        $stmt->bind_result($stock_atual);
        $stmt->fetch();
        $stmt->close();

        if ($item['quantidade'] > $stock_atual) {
            $erro_stock = true;
            break;
        }
    }

    if (!$erro_stock) {
        $stmt = $conn->prepare("INSERT INTO purchases (user_id, event_id, quantidade, data_compra) VALUES (?, ?, ?, ?)");
        $update = $conn->prepare("UPDATE events SET stock = stock - ? WHERE id = ?");

        foreach ($items as $item) {
            $stmt->bind_param("iiis", $user_id, $item['event_id'], $item['quantidade'], $now);
            $stmt->execute();

            $update->bind_param("ii", $item['quantidade'], $item['event_id']);
            $update->execute();
        }
        $stmt->close();
        $update->close();

        $conn->query("DELETE FROM cart WHERE user_id = $user_id");

        $conn->commit();

        echo "<!DOCTYPE html>
        <html lang='pt'>
        <head>
            <meta charset='UTF-8'>
            <meta http-equiv='refresh' content='3;url=profile.php'>
            <title>Compra com Sucesso</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body>
        <div class='container text-center mt-5'>
            <h1 class='text-success'>Compra com sucesso!</h1>
            <p>Será redirecionado para o seu perfil em instantes...</p>
            <a href='profile.php' class='btn btn-primary mt-3'>Ir agora</a>
        </div>
        </body>
        </html>";
        exit();
    } else {
        $conn->rollback();
        $erro_mensagem = "Um ou mais itens não têm stock suficiente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>
<div class="container py-4">
    <h1 class="text-center text-white mb-4">Finalizar Compra</h1>

    <?php if (!$valid || isset($erro_mensagem)): ?>
        <div class="alert alert-danger text-center">
            <?= isset($erro_mensagem) ? $erro_mensagem : 'Alguns itens excedem o stock disponível.' ?>
        </div>
    <?php endif; ?>

    <p class="text-white text-center">Tem a certeza de que pretende concluir a compra dos seguintes itens?</p>

    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover table-striped bg-light">
            <thead class="table-dark text-center">
                <tr>
                    <th>Imagem</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody class="text-center align-middle">
                <?php foreach ($items as $item): ?>
                    <?php
                        // Buscar imagem do evento
                        $stmt_img = $conn->prepare("SELECT imagem FROM events WHERE id = ?");
                        $stmt_img->bind_param("i", $item['event_id']);
                        $stmt_img->execute();
                        $result_img = $stmt_img->get_result();
                        $event_img = $result_img->fetch_assoc();
                        $stmt_img->close();
                    ?>
                    <tr>
                        <td>
                            <?php if (!empty($event_img['imagem'])): ?>
                                <img src="<?= htmlspecialchars($event_img['imagem']) ?>" alt="Evento" class="img-thumbnail" style="max-width: 120px;">
                            <?php else: ?>
                                <span class="text-muted">Sem imagem</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $item['quantidade'] ?></td>
                        <td>€<?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h4 class="text-white text-center mb-4">Total: €<?= number_format($total, 2, ',', '.') ?></h4>

    <form method="POST" class="text-center">
        <button type="submit" class="btn btn-success btn-lg me-2" <?= !$valid ? 'disabled' : '' ?>>✅ Confirmar Compra</button>
        <a href="cart.php" class="btn btn-outline-light btn-lg">❌ Cancelar</a>
    </form>
</div>
</body>
</html>
