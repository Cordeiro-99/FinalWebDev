<?php
session_start();
include 'config.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$order_param = isset($_GET['order']) ? $_GET['order'] : 'data_asc';

// Query para o carrossel (3 eventos mais próximos) permanece fixa:
$sql = "SELECT * FROM events ORDER BY data ASC LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$eventos_para_carrossel = $result->fetch_all(MYSQLI_ASSOC);

// Normaliza termos de mês
$meses = [
    'janeiro' => '01',
    'fevereiro' => '02',
    'março' => '03',
    'abril' => '04',
    'maio' => '05',
    'junho' => '06',
    'julho' => '07',
    'agosto' => '08',
    'setembro' => '09',
    'outubro' => '10',
    'novembro' => '11',
    'dezembro' => '12'
];
$mes_escolhido = strtolower($search);
$mes_numero = $meses[$mes_escolhido] ?? null;

// Define ORDER BY da listagem segundo o que foi pedido no GET
switch($order_param) {
    case 'data_asc':
        $order_sql = "data ASC";
        break;
    case 'data_desc':
        $order_sql = "data DESC";
        break;
    case 'preco_asc':
        $order_sql = "preco ASC";
        break;
    case 'preco_desc':
        $order_sql = "preco DESC";
        break;
    default:
        $order_sql = "data ASC";
}

// Busca todos os eventos para a lista filtrada e ordenada
$sqlAll = "SELECT * FROM events ORDER BY $order_sql";
$stmtAll = $conn->prepare($sqlAll);
$stmtAll->execute();
$resultAll = $stmtAll->get_result();

$eventos_filtrados = [];
while ($row = $resultAll->fetch_assoc()) {
    $titulo = strtolower($row['titulo']);
    $data_evento = $row['data'];
    $ano_evento = date('Y', strtotime($data_evento));
    $mes_evento = date('m', strtotime($data_evento));

    if (
        empty($search) ||
        str_contains($titulo, strtolower($search)) ||
        $data_evento === $search ||
        $ano_evento === $search ||
        ($mes_numero && $mes_evento === $mes_numero)
    ) {
        $eventos_filtrados[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Eventos - Loja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/loja.css">
    <script src="https://kit.fontawesome.com/142f474ea8.js" crossorigin="anonymous"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">👤 <?= htmlspecialchars($_SESSION['username']) ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">🚪 Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">👤 Login</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">🛒 Carrinho</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<main class="flex-grow-1">
    <div class="container mt-4">
        <h1 class="text-center mb-3">Próximos Eventos</h1>
        <div id="eventCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($eventos_para_carrossel as $index => $evento): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="card mx-auto" style="max-width: 600px;">
                            <?php if (!empty($evento['imagem'])): ?>
                                <img src="<?= htmlspecialchars($evento['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($evento['titulo']) ?>">
                            <?php endif; ?>
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($evento['titulo']) ?></h5>
                                <p class="card-text mb-1"><strong>Data:</strong> <?= date('d/m/Y', strtotime($evento['data'])) ?> às <?= date('H:i', strtotime($evento['hora'])) ?></p>
                                <a href="evento_detalhes.php?id=<?= $evento['id'] ?>" class="btn btn-primary mt-2">Ver Detalhes</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Seguinte</span>
            </button>
        </div>
    </div>

    <div class="container my-5">
        <h1 class="mb-4 text-center">Lista de Eventos</h1>

        <form class="row g-2 mb-3 align-items-center" method="GET" action="index.php" id="searchForm">
            <div class="col-md-5">
                <input class="form-control" type="search" placeholder="Pesquisar por título, ano ou mês"
                    aria-label="Search" name="search" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="order" id="orderSelect">
                    <option value="data_asc" <?= $order_param === 'data_asc' ? 'selected' : '' ?>>Data mais próxima</option>
                    <option value="data_desc" <?= $order_param === 'data_desc' ? 'selected' : '' ?>>Data mais distante</option>
                    <option value="preco_asc" <?= $order_param === 'preco_asc' ? 'selected' : '' ?>>Preço mais baixo</option>
                    <option value="preco_desc" <?= $order_param === 'preco_desc' ? 'selected' : '' ?>>Preço mais alto</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-success w-100" type="submit">Pesquisar</button>
            </div>
        </form>

        <?php if (!empty($search)): ?>
            <a href="index.php" class="btn btn-outline-secondary mb-4">Limpar pesquisa</a>
        <?php endif; ?>

        <div class="row" id="eventList">
            <?php if (count($eventos_filtrados) > 0): ?>
                <?php foreach ($eventos_filtrados as $row): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($row['imagem'])): ?>
                                <img src="<?= htmlspecialchars($row['imagem']) ?>" class="card-img-top" alt="Imagem do evento">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($row['titulo']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($row['descricao']) ?></p>
                                <p><strong>Data:</strong> <?= htmlspecialchars($row['data']) ?> às
                                    <?= date('H:i', strtotime($row['hora'])) ?></p>
                                <p><strong>Local:</strong> <?= htmlspecialchars($row['local']) ?></p>
                                <p><strong>Preço:</strong> €<?= number_format($row['preco'], 2, ',', '.') ?></p>
                                <p><strong>Bilhetes disponíveis:</strong> <?= $row['stock'] ?></p>
                                <div class="mt-auto">
                                    <a href="evento_detalhes.php?id=<?= $row['id'] ?>" class="btn btn-info w-100 mb-2">Ver
                                        Detalhes</a>
                                    <?php if ($row['stock'] > 0): ?>
                                        <form class="d-flex add-to-cart-form" data-id="<?= $row['id'] ?>">
                                            <input type="number" name="quantidade" value="1" min="1" max="<?= $row['stock'] ?>"
                                                class="form-control me-2 quantidade" style="width: 80px;">
                                            <button type="submit" class="btn btn-success">Adicionar</button>
                                        </form>
                                        <div class="feedback text-success small mt-1" style="display: none;">Adicionado ao carrinho!
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>Esgotado</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Nenhum evento encontrado.</p>
            <?php endif; ?>
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
<script>
document.addEventListener("DOMContentLoaded", function () {
    const forms = document.querySelectorAll('.add-to-cart-form');
    const orderSelect = document.getElementById('orderSelect');
    const form = document.getElementById('searchForm');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const eventId = this.dataset.id;
            const quantidade = this.querySelector('.quantidade').value;
            const feedback = this.nextElementSibling;

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `event_id=${eventId}&quantidade=${quantidade}`
            })
            .then(response => response.text())
            .then(data => {
                feedback.textContent = "Adicionado ao carrinho!";
                feedback.style.display = "block";
                setTimeout(() => feedback.style.display = "none", 3000);
            })
            .catch(error => {
                feedback.textContent = "Erro ao adicionar!";
                feedback.classList.remove("text-success");
                feedback.classList.add("text-danger");
                feedback.style.display = "block";
            });
        });
    });

    orderSelect.addEventListener('change', function () {
        const order = this.value;
        const search = form.querySelector('input[name="search"]').value;

        fetch(`index.php?search=${encodeURIComponent(search)}&order=${order}`)
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const newEventList = doc.getElementById('eventList');
                document.getElementById('eventList').innerHTML = newEventList.innerHTML;
            })
            .catch(error => console.error('Erro ao carregar eventos:', error));
    });
});
</script>


</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
