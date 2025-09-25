<?php
$servername = "127.0.0.1";
$port = 3310;
$username = "root";
$password = "root";
$dbname = "projetofinal";

// Cria a ligação
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Verificar conexão
if (!$conn) {
    error_log("Falha na conexão com o banco de dados: " . mysqli_connect_error());
    exit("Erro ao conectar ao banco de dados. Tente novamente mais tarde.");
}
?>
