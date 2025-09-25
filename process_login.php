<?php
error_log("POST no login: " . print_r($_POST, true));

// Incluir o arquivo de configuração para conexão com o banco de dados
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Nome de utilizador e password são obrigatórios.']);
        exit;
    }

    // Consulta segura usando MySQLi
    if ($stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?")) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) { // Verifica a senha
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role']; 
            $_SESSION['username'] = $username;
            
            echo json_encode(['success' => true, 'message' => 'Login bem-sucedido!', 'redirect' => 'profile.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nome de utilizador ou senha inválidos.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao executar a consulta.']);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}
?>
