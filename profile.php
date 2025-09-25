<?php
session_start();
require_once "config.php";

// Verifica autenticação
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Atualizar dados do utilizador e senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_username'], $_POST['novo_email'])) {
    $novo_username = trim($_POST['novo_username']);
    $novo_email = trim($_POST['novo_email']);

    // Atualizar username e email
    $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $novo_username, $novo_email, $user_id);
    $updateStmt->execute();
    $updateStmt->close();

    $_SESSION['username'] = $novo_username;

    // Verificar se vai alterar password
    if (!empty($_POST['password_atual']) && !empty($_POST['nova_password']) && !empty($_POST['confirmar_password'])) {
        $password_atual = $_POST['password_atual'];
        $nova_password = $_POST['nova_password'];
        $confirmar_password = $_POST['confirmar_password'];

        if ($nova_password !== $confirmar_password) {
            echo "<script>alert('Nova password e confirmação não coincidem.');</script>";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($password_hash);
            $stmt->fetch();
            $stmt->close();

            if (password_verify($password_atual, $password_hash)) {
                $hash_nova = password_hash($nova_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hash_nova, $user_id);
                $stmt->execute();
                $stmt->close();
                echo "<script>alert('Password atualizada com sucesso.');</script>";
            } else {
                echo "<script>alert('Password atual incorreta.');</script>";
            }
        }
    }

    header("Location: profile.php");
    exit();
}

// Cancelar compra pendente e repor stock
if (isset($_GET['cancelar']) && is_numeric($_GET['cancelar'])) {
    $cancel_id = (int) $_GET['cancelar'];

    $checkStmt = $conn->prepare("SELECT event_id, quantidade FROM purchases WHERE id = ? AND user_id = ? AND estado = 'Pendente'");
    $checkStmt->bind_param("ii", $cancel_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 1) {
        $compra = $result->fetch_assoc();
        $event_id = $compra['event_id'];
        $quantidade = $compra['quantidade'];

        $cancelStmt = $conn->prepare("UPDATE purchases SET estado = 'Cancelado' WHERE id = ? AND user_id = ?");
        $cancelStmt->bind_param("ii", $cancel_id, $user_id);
        $cancelStmt->execute();
        $cancelStmt->close();

        $updateStockStmt = $conn->prepare("UPDATE events SET stock = stock + ? WHERE id = ?");
        $updateStockStmt->bind_param("ii", $quantidade, $event_id);
        $updateStockStmt->execute();
        $updateStockStmt->close();
    }

    $checkStmt->close();
    header("Location: profile.php");
    exit();
}

$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmtCompras = $conn->prepare("SELECT p.id AS compra_id, e.titulo, e.data, e.hora, e.local, e.preco, p.quantidade, p.data_compra, p.estado FROM purchases p JOIN events e ON p.event_id = e.id WHERE p.user_id = ? ORDER BY p.data_compra DESC");
$stmtCompras->bind_param("i", $user_id);
$stmtCompras->execute();
$compras = $stmtCompras->get_result();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Perfil do Utilizador</title>
    <link rel="stylesheet" href="css/profile.css">
    <script src="https://kit.fontawesome.com/142f474ea8.js" crossorigin="anonymous"></script>
    <script>
        function toggleEditar() {
            document.querySelector('.edit-form').classList.toggle('visivel');
        }
    </script>
</head>

<body>
    <main class="main-content">
    <div class="profile-container">
        <h1>Perfil do Utilizador</h1>
        <div class="info-box">
            <p><strong>Utilizador:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Função:</strong> <?= htmlspecialchars($user['role']) ?></p>
            <button class="btn" onclick="toggleEditar()">✏️ Alterar Dados</button>
            <form method="POST" class="edit-form">
                <label>Utilizador: <input type="text" name="novo_username"
                        value="<?= htmlspecialchars($user['username']) ?>" required></label>
                <label>Email: <input type="email" name="novo_email" value="<?= htmlspecialchars($user['email']) ?>"
                        required></label>

                <h3>Alterar Password</h3>
                <label>Password Atual: <input type="password" name="password_atual"></label>
                <label>Nova Password: <input type="password" name="nova_password"></label>
                <label>Confirmar Nova Password: <input type="password" name="confirmar_password"></label>
                <button type="submit" class="btn">📂 Guardar Alterações</button>
            </form>

            <div class="botoes">
                <a href="index.php" class="btn">🏠 Loja</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin/admin.php" class="btn">🔧 Painel Admin</a>
                <?php endif; ?>
                <form action="logout.php" method="post">
                    <button type="submit" class="btn logout">🚪 Sair</button>
                </form>
            </div>
        </div>

        <h2>Histórico de Compras</h2>
        <?php if ($compras->num_rows > 0): ?>
            <table class="compras">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Local</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Total</th>
                        <th>Data da Compra</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $compras->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['titulo']) ?></td>
                            <td><?= htmlspecialchars($row['data']) ?></td>
                            <td><?= htmlspecialchars($row['hora']) ?></td>
                            <td><?= htmlspecialchars($row['local']) ?></td>
                            <td>€<?= number_format($row['preco'], 2, ',', '.') ?></td>
                            <td><?= $row['quantidade'] ?></td>
                            <td>€<?= number_format($row['preco'] * $row['quantidade'], 2, ',', '.') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['data_compra'])) ?></td>
                            <td><?= $row['estado'] ?></td>
                            <td>
                                <?php if ($row['estado'] === 'Pendente'): ?>
                                    <a href="profile.php?cancelar=<?= $row['compra_id'] ?>" class="btn btn-cancelar">Cancelar</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="mensagem-vazia">Ainda não compraste nenhum bilhete.</p>
        <?php endif; ?>
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
    <script>
document.querySelector('.edit-form').addEventListener('submit', function(e) {
    const atual = document.querySelector('input[name="password_atual"]').value.trim();
    const nova = document.querySelector('input[name="nova_password"]').value.trim();
    const confirmar = document.querySelector('input[name="confirmar_password"]').value.trim();

    // Só valida se algum campo de password for preenchido
    if (atual || nova || confirmar) {
        if (!atual || !nova || !confirmar) {
            alert("Preencha todos os campos de password.");
            e.preventDefault();
            return;
        }

        if (nova.length < 6) {
            alert("A nova password deve ter pelo menos 6 caracteres.");
            e.preventDefault();
            return;
        }

        if (nova !== confirmar) {
            alert("A nova password e a confirmação não coincidem.");
            e.preventDefault();
            return;
        }
    }
});
</script>

</body>

</html>
