<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID do evento inválido.");
}

$evento_id = intval($_GET['id']);

// Busca os dados do evento
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $evento_id);
$stmt->execute();
$result = $stmt->get_result();
$evento = $result->fetch_assoc();

if (!$evento) {
    die("Evento não encontrado.");
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
        $imagemPath = $evento['imagem']; // Mantém a imagem atual por padrão

        // Novo upload de imagem, se houver
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
            } else {
                $imagemPath = str_replace('../', '', $imagemPath);
            }
        }

        if (!isset($erro)) {
            $stmt = $conn->prepare("UPDATE events SET titulo = ?, descricao = ?, data = ?, hora = ?, local = ?, preco = ?, stock = ?, imagem = ? WHERE id = ?");
            $stmt->bind_param("sssssdisi", $nome, $descricao, $data, $hora, $local, $preco, $stock, $imagemPath, $evento_id);

            if ($stmt->execute()) {
                $sucesso = "Evento atualizado com sucesso!";
                // Atualiza os dados para refletir no formulário
                $evento = array_merge($evento, [
                    'titulo' => $nome,
                    'descricao' => $descricao,
                    'data' => $data,
                    'hora' => $hora,
                    'local' => $local,
                    'preco' => $preco,
                    'stock' => $stock,
                    'imagem' => $imagemPath
                ]);
            } else {
                $erro = "Erro ao atualizar evento: " . $stmt->error;
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
    <title>Editar Evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_criar.css">
</head>
<body>
<div class="container my-5">
    <h1 class="mb-4">Editar Evento</h1>

    <?php if (isset($sucesso)): ?>
        <div class="alert alert-success"><?= $sucesso ?></div>
    <?php elseif (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm">
        <form action="eventos_editar.php?id=<?= $evento_id ?>" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Evento:</label>
                <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($evento['titulo']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição:</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="4" required><?= htmlspecialchars($evento['descricao']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="data" class="form-label">Data:</label>
                <input type="date" name="data" id="data" class="form-control" value="<?= $evento['data'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="hora" class="form-label">Hora:</label>
                <input type="time" name="hora" id="hora" class="form-control" value="<?= $evento['hora'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="local" class="form-label">Local:</label>
                <input type="text" name="local" id="local" class="form-control" value="<?= htmlspecialchars($evento['local']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="preco" class="form-label">Preço (€):</label>
                <input type="number" name="preco" id="preco" class="form-control" step="0.01" min="0" value="<?= number_format($evento['preco'], 2, '.', '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="stock" class="form-label">Stock de Bilhetes:</label>
                <input type="number" name="stock" id="stock" class="form-control" min="0" value="<?= $evento['stock'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="imagem" class="form-label">Imagem do Evento (opcional):</label>
                <input type="file" name="imagem" id="imagem" class="form-control" accept="image/*">
                <?php if (!empty($evento['imagem'])): ?>
                    <div class="mt-2">
                        <img src="../<?= $evento['imagem'] ?>" alt="Imagem atual" class="img-fluid rounded" style="max-height: 150px;">
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Atualizar Evento</button>
            <a href="admin.php" class="btn btn-secondary ms-2">← Voltar</a>
        </form>
    </div>
</div>
</body>
</html>
