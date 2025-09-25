<?php
session_start();
include '../config.php';

// Verifica se o utilizador é admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Atualiza estado da compra e atualiza stock se cancelar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_estado'])) {
    $nova_acao = $_POST['estado'];
    $purchase_id = (int) $_POST['purchase_id'];

    if (in_array($nova_acao, ['Pendente', 'Confirmado', 'Cancelado'])) {
        $stmt = $conn->prepare("SELECT estado, quantidade, event_id FROM purchases WHERE id = ?");
        $stmt->bind_param("i", $purchase_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $compra_atual = $result->fetch_assoc();
        $stmt->close();

        if ($compra_atual) {
            $estado_atual = $compra_atual['estado'];
            $quantidade = (int) $compra_atual['quantidade'];
            $event_id = (int) $compra_atual['event_id'];

            if ($estado_atual !== 'Cancelado' && $nova_acao === 'Cancelado') {
                $stmt = $conn->prepare("UPDATE events SET stock = stock + ? WHERE id = ?");
                $stmt->bind_param("ii", $quantidade, $event_id);
                $stmt->execute();
                $stmt->close();
            } elseif ($estado_atual === 'Cancelado' && $nova_acao !== 'Cancelado') {
                $stmt = $conn->prepare("SELECT stock FROM events WHERE id = ?");
                $stmt->bind_param("i", $event_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $event = $result->fetch_assoc();
                $stmt->close();

                if ($event && $event['stock'] >= $quantidade) {
                    $stmt = $conn->prepare("UPDATE events SET stock = stock - ? WHERE id = ?");
                    $stmt->bind_param("ii", $quantidade, $event_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $stmt = $conn->prepare("UPDATE purchases SET estado = ? WHERE id = ?");
            $stmt->bind_param("si", $nova_acao, $purchase_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Eliminar compras canceladas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_purchase_id'])) {
    $delete_id = (int) $_POST['delete_purchase_id'];

    $stmt = $conn->prepare("SELECT estado FROM purchases WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $compra = $result->fetch_assoc();
    $stmt->close();

    if ($compra && $compra['estado'] === 'Cancelado') {
        $stmt = $conn->prepare("DELETE FROM purchases WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin.php");
    exit();
}

// Funções auxiliares
function fetchUsers($conn) {
    return $conn->query("SELECT * FROM users");
}
function fetchEvents($conn) {
    return $conn->query("SELECT * FROM events ORDER BY data ASC, hora ASC");
}

function fetchPurchases($conn) {
    $sql = "SELECT purchases.id, users.username, events.titulo, purchases.quantidade, purchases.data_compra, purchases.estado
            FROM purchases
            JOIN users ON purchases.user_id = users.id
            JOIN events ON purchases.event_id = events.id
            ORDER BY purchases.data_compra DESC";
    return $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>

    <h1>Painel de Administração</h1>

    <!-- Botões de navegação -->
    <div style="margin-bottom: 20px; display: flex; gap: 10px;">
        <!-- Botão para voltar à loja -->
        <a href="../index.php" class="btn-navegacao">🏠 Ir para Loja</a>

        <!-- Botão para voltar ao perfil -->
        <a href="../profile.php" class="btn-navegacao">👤 Ir para Perfil</a>
    </div>


    <!-- Utilizadores -->
    <h2>Utilizadores</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Função</th>
            <th>Ações</th>
        </tr>
        <?php
        $users = fetchUsers($conn);
        if ($users && $users->num_rows > 0) {
            while ($user = $users->fetch_assoc()) {
                echo "<tr>
                        <td>{$user['id']}</td>
                        <td>" . htmlspecialchars($user['username']) . "</td>
                        <td>" . htmlspecialchars($user['email']) . "</td>
                        <td>" . htmlspecialchars($user['role']) . "</td>
                        <td>
                            <a href='edit_user.php?id={$user['id']}'>Editar</a> |
                            <a href='delete_user.php?id={$user['id']}' onclick=\"return confirm('Tem certeza que deseja eliminar este utilizador?')\">Eliminar</a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>Sem utilizadores.</td></tr>";
        }
        ?>
    </table>

    <!-- Eventos -->
    <h2>Eventos</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Data</th>
            <th>Hora</th>
            <th>Local</th>
            <th>Preço</th>
            <th>Ações</th>
        </tr>
        <?php
        $events = fetchEvents($conn);
        if ($events && $events->num_rows > 0) {
            while ($event = $events->fetch_assoc()) {
                echo "<tr>
                        <td>{$event['id']}</td>
                        <td>" . htmlspecialchars($event['titulo']) . "</td>
                        <td>" . htmlspecialchars($event['data']) . "</td>
                        <td>" . htmlspecialchars(date('H:i', strtotime($event['hora']))) . "</td>
                        <td>" . htmlspecialchars($event['local']) . "</td>
                        <td>€" . number_format($event['preco'], 2, ',', '.') . "</td>
                        <td>
                            <a href='eventos_editar.php?id={$event['id']}'>Editar</a> |
                            <a href='eventos_deletar.php?id={$event['id']}' onclick=\"return confirm('Tem certeza que deseja remover este evento?')\">Eliminar</a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Sem eventos.</td></tr>";
        }
        ?>
    </table>

    <div style="margin-top: 10px;">
        <a href="eventos_criar.php" class="btn-primary">Adicionar Novo Evento</a>
    </div>

    <!-- Compras -->
    <h2>Compras de Bilhetes</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Utilizador</th>
            <th>Evento</th>
            <th>Quantidade</th>
            <th>Data de Compra</th>
            <th>Estado</th>
            <th>Ação</th>
        </tr>
       <?php

$compras = fetchPurchases($conn);
if ($compras && $compras->num_rows > 0) {
    while ($compra = $compras->fetch_assoc()) {
        $classe_estado = '';
        if ($compra['estado'] === 'Pendente') $classe_estado = 'estado-pendente';
        else if ($compra['estado'] === 'Confirmado') $classe_estado = 'estado-confirmado';
        else if ($compra['estado'] === 'Cancelado') $classe_estado = 'estado-cancelado';

        echo "<tr>
                <td>{$compra['id']}</td>
                <td>" . htmlspecialchars($compra['username']) . "</td>
                <td>" . htmlspecialchars($compra['titulo']) . "</td>
                <td>{$compra['quantidade']}</td>
                <td>{$compra['data_compra']}</td>
                <td class='$classe_estado'>{$compra['estado']}</td>
                <td>
                    <form method='post' action='admin.php' style='display:inline-block; margin-right:5px;'>
                        <input type='hidden' name='purchase_id' value='{$compra['id']}'>
                        <select name='estado'>
                            <option value='Pendente'" . ($compra['estado'] === 'Pendente' ? ' selected' : '') . ">Pendente</option>
                            <option value='Confirmado'" . ($compra['estado'] === 'Confirmado' ? ' selected' : '') . ">Confirmado</option>
                            <option value='Cancelado'" . ($compra['estado'] === 'Cancelado' ? ' selected' : '') . ">Cancelado</option>
                        </select>
                        <button type='submit' name='alterar_estado'>Atualizar</button>
                    </form>";
        if ($compra['estado'] === 'Cancelado') {
            echo "<form method='post' action='admin.php' style='display:inline-block;' onsubmit=\"return confirm('Eliminar esta compra cancelada?');\">
                    <input type='hidden' name='delete_purchase_id' value='{$compra['id']}'>
                    <button type='submit'>🗑 Eliminar</button>
                  </form>";
        }
        echo "</td></tr>";
    }
}
?>
    </table>

</body>

</html>

<?php $conn->close(); ?>