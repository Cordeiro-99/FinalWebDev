<?php
include 'config.php';
session_start();

$response = ['success' => false, 'message' => 'Erro desconhecido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    // Força o tipo de utilizador como 'user'
    $role = 'user';

    // Validação do nome de utilizador
    if (strlen($username) < 3) {
        $response['message'] = 'O nome de utilizador deve ter pelo menos 3 caracteres.';
        echo json_encode($response);
        exit;
    }

    // Validação do e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Por favor, insira um e-mail válido.';
        echo json_encode($response);
        exit;
    }

    // Validação da senha
    if (strlen($password) < 6) {
        $response['message'] = 'A senha deve ter pelo menos 6 caracteres.';
        echo json_encode($response);
        exit;
    }

    // Verifica se a senha e a confirmação são iguais
    if ($password !== $confirmPassword) {
        $response['message'] = 'As senhas não correspondem.';
        echo json_encode($response);
        exit;
    }

    // Verifica se o nome de usuário ou o email já existem
    $stmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $response['message'] = 'Nome de utilizador ou email já está em uso.';
        echo json_encode($response);
        exit;
    }

    // Criptografa a senha
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insere o novo usuário na base de dados
    $stmt = $conn->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $username, $email, $hashedPassword, $role);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        $response['success'] = true;
        $response['message'] = 'Registro bem-sucedido!';
    } else {
        $response['message'] = 'Erro ao registrar o user: ' . $conn->error; 
    }

    echo json_encode($response);
}
?>
