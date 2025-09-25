<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("ID de utilizador não fornecido.");
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $role, $id);
    $stmt->execute();

    header("Location: admin.php");
    exit();
}

$result = $conn->query("SELECT * FROM users WHERE id = $id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Utilizador</title>
</head>
<body>
    <h1>Editar Utilizador</h1>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo $user['username']; ?>" required><br>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $user['email']; ?>" required><br>

        <label>Role:</label>
        <select name="role">
            <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User</option>
            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
        </select><br>

        <button type="submit">Guardar Alterações</button>
    </form>
</body>
</html>
