<?php
$host = 'localhost';
$dbname = 'bd_fina';
$usuario = 'root';
$senha = '';

$conn = new mysqli($host, $usuario, $senha, $dbname);

if ($conn->connect_error) {
    die('Erro de conexão: ' . $conn->connect_error);
}
?>