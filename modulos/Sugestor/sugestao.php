<?php
session_start();
include_once '../../assets/bd/conexao.php';

$perfil = $_GET['perfil_financeiro']; 

switch ($perfil) {
    case 'Conservador':
        $sugestoes = include 'perfilConservador.php';
        break;
    case 'Moderado':
        $sugestoes = include 'perfilModerado.php';
        break;
    case 'Agressivo':
        $sugestoes = include 'perfilAgressivo.php';
        break;
    default:
        $sugestoes = [];
}

foreach ($sugestoes as $sugestao) {
    echo "<li><strong>{$sugestao['nome']}</strong>: {$sugestao['descricao']} <a href='{$sugestao['link']}' target='_blank'>Saiba mais</a></li>";
}