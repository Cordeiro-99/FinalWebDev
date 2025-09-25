<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $local = trim($_POST['local']);
    $preco = floatval($_POST['preco']);
    $stock = intval($_POST['stock']);

    if (!empty($nome) && !empty($descricao) && !empty($data) && !empty($hora) && !empty($local)) {
        $imagemPath = null;

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $imagemNome = uniqid('evento_', true) . '.' . $ext;
            $imagemPath = $uploadDir . $imagemNome;

            if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $imagemPath)) {
                $erro = "Erro ao fazer upload da imagem.";
            }
        }

        if (!isset($erro)) {
            $imagemRelativa = str_replace('../', '', $imagemPath);

            $stmt = $conn->prepare("INSERT INTO events (titulo, descricao, data, hora, local, preco, stock, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdis", $nome, $descricao, $data, $hora, $local, $preco, $stock, $imagemRelativa);

            if ($stmt->execute()) {
                $sucesso = "Evento criado com sucesso!";
            } else {
                $erro = "Erro ao criar evento: " . $stmt->error;
            }

            $stmt->close();
        }
    } else {
        $erro = "Todos os campos são obrigatórios.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Criar Evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_criar.css">
</head>
<body>
<div class="container my-5">
    <h1 class="mb-4">Criar Novo Evento</h1>

    <?php if (isset($sucesso)): ?>
        <div class="alert alert-success"><?= $sucesso ?></div>
    <?php elseif (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm">
        <form action="eventos_criar.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Evento:</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição:</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="data" class="form-label">Data:</label>
                <input type="date" name="data" id="data" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="hora" class="form-label">Hora:</label>
                <input type="time" name="hora" id="hora" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="local" class="form-label">Local:</label>
                <input type="text" name="local" id="local" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="preco" class="form-label">Preço (€):</label>
                <input type="number" name="preco" id="preco" class="form-control" step="0.01" min="0" required>
            </div>

            <div class="mb-3">
                <label for="stock" class="form-label">Stock de Bilhetes:</label>
                <input type="number" name="stock" id="stock" class="form-control" min="0" required>
            </div>

            <div class="mb-3">
                <label for="imagem" class="form-label">Imagem do Evento:</label>
                <input type="file" name="imagem" id="imagem" class="form-control" accept="image/*" required>
            </div>

            <button type="submit" class="btn btn-primary">Criar Evento</button>
            <a href="admin.php" class="btn btn-secondary ms-2">← Voltar ao Painel</a>
        </form>
    </div>
</div>
</body>
</html>
