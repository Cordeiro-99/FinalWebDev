<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo "Evento não encontrado.";
    exit();
}

$evento = $result->fetch_assoc();

// Lógica de adicionar ao carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_carrinho'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $quantidade = max(1, intval($_POST['quantidade'] ?? 1));

    $stmt = $conn->prepare("SELECT id, quantidade FROM cart WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nova_quantidade = $row['quantidade'] + $quantidade;
        $cart_id = $row['id'];

        $update = $conn->prepare("UPDATE cart SET quantidade = ? WHERE id = ?");
        $update->bind_param("ii", $nova_quantidade, $cart_id);
        $update->execute();
        $update->close();
    } else {
        $insert = $conn->prepare("INSERT INTO cart (user_id, event_id, quantidade) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $user_id, $id, $quantidade);
        $insert->execute();
        $insert->close();
    }

    $stmt->close();
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($evento['titulo']) ?> | Bilheteira Online</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/detalhes.css">
    <script src="https://kit.fontawesome.com/142f474ea8.js" crossorigin="anonymous"></script>
</head>
<body>
    <main class="py-5">
        <div class="container">
            <a href="index.php" class="btn btn-outline-secondary mb-4"><i class="fa fa-arrow-left"></i> Voltar</a>

            <div class="row g-5 align-items-start">
                <div class="col-md-6">
                    <img src="<?= htmlspecialchars($evento['imagem']) ?>" alt="Imagem do evento <?= htmlspecialchars($evento['titulo']) ?>" class="img-fluid rounded shadow-sm">
                </div>

                <div class="col-md-6">
                    <h1 class="mb-3"><?= htmlspecialchars($evento['titulo']) ?></h1>
                    <p class="lead"><?= nl2br(htmlspecialchars($evento['descricao'])) ?></p>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item"><strong>Data:</strong> <?= date("d/m/Y", strtotime($evento['data'])) ?></li>
                        <li class="list-group-item"><strong>Hora:</strong> <?= date("H:i", strtotime($evento['hora'])) ?></li>
                        <li class="list-group-item"><strong>Local:</strong> <?= htmlspecialchars($evento['local']) ?></li>
                        <li class="list-group-item"><strong>Preço:</strong> €<?= number_format($evento['preco'], 2, ',', '.') ?></li>
                    </ul>

                    <form method="post" class="d-flex align-items-center gap-3">
                        <label for="quantidade" class="form-label mb-0">Quantidade:</label>
                        <input type="number" name="quantidade" id="quantidade" value="1" min="1" class="form-control w-auto" required>
                        <button type="submit" name="adicionar_carrinho" class="btn btn-success">
                            <i class="fa fa-cart-plus me-1"></i> Adicionar ao Carrinho
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
<footer class="footer mt-5 custom-footer border-top">
        <div class="container text-center py-3">
            <p class="mb-1">© <?= date("Y") ?> - Bilheteira Online</p>
            <div class="social-icons">
                <a href="https://www.instagram.com" target="_blank" class="mx-2"><i class="fa fa-instagram"></i></a>
                <a href="https://www.facebook.com" target="_blank" class="mx-2"><i class="fa fa-facebook"></i></a>
                <a href="https://www.twitter.com" target="_blank" class="mx-2"><i class="fa fa-twitter"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
