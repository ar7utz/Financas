<?php
//Incluir o arquivo de conexão com o banco de dados
include ('../../assets/bd/conexao.php');

//Consultar o banco de dados para obter entradas, saídas e saldo total
$sql = "SELECT SUM(CASE WHEN valor > 0 THEN valor ELSE 0 END) AS entradas,
               SUM(CASE WHEN valor < 0 THEN valor ELSE 0 END) AS saidas
        FROM transacoes";

$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    $row = $resultado->fetch_assoc();
    $entradas = $row['entradas'];
    $saidas = $row['saidas'];
    $saldo = $entradas + $saidas;
    
    //Retornar os valores como json
    echo json_encode([
        'entradas' => $entradas,
        'saidas' => abs($saidas),
        'saldo' => $saldo
    ]);
} else {
    //Se não houver transações, retornar um objeto vazio
    echo json_encode([]);
}

$conn->close();
?>
