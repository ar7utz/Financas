<?php

session_start();

include_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit;
}


$openai_api_key = '';

$perfil = $_GET['perfil'] ?? 'Conservador';

$sugestoes = [
    'Conservador' => [
        ['nome' => 'Tesouro Selic', 'link' => 'https://www.tesourodireto.com.br/',  
        'descricao' => 'Investimento de baixo risco e liquidez diária.'],
        ['nome' => 'CDB de grandes bancos', 'link' => 'https://www.bcb.gov.br/estabilidadefinanceira/cdb', 'descricao' => 'Renda fixa, protegido pelo FGC.'],
    ],
    'Moderado' => [
        ['nome' => 'Tesouro IPCA+', 'link' => 'https://www.tesourodireto.com.br/', 'descricao' => 'Proteção contra inflação.'],
        ['nome' => 'Fundos Multimercado', 'link' => 'https://www.anbima.com.br/pt_br/informar/fundos-multimercado.htm', 'descricao' => 'Diversificação com risco moderado.'],
    ],
    'Agressivo' => [
        ['nome' => 'Ações', 'link' => 'https://www.b3.com.br/', 'descricao' => 'Potencial de maior retorno, maior risco.'],
        ['nome' => 'Fundos de ações', 'link' => 'https://www.anbima.com.br/pt_br/informar/fundos-de-acoes.htm', 'descricao' => 'Gestão profissional de carteira de ações.'],
    ],
];

// Monta o texto das sugestões para o prompt
$sugestoes_texto = "";
foreach ($sugestoes[$perfil] as $s) {
    $sugestoes_texto .= "- {$s['nome']}: {$s['descricao']} (Mais informações: {$s['link']})\n";
}

$prompt = "Considere o perfil financeiro do usuário: $perfil. Sugira investimentos adequados usando apenas as opções abaixo, explique de forma simples e inclua os links fornecidos:\n$sugestoes_texto";

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $openai_api_key,
]);
$data = [
    "model" => "gpt-3.5-turbo",
    "messages" => [
        ["role" => "system", "content" => "Você é um assistente financeiro que só pode sugerir investimentos a partir da lista fornecida pelo usuário, sempre incluindo os links indicados."],
        ["role" => "user", "content" => $prompt]
    ],
    "max_tokens" => 300,
    "temperature" => 0.7,
];
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

echo "<h2>Sugestão de Investimento para perfil: <span style='color:#FFD600;'>$perfil</span></h2>";
if (isset($result['choices'][0]['message']['content'])) {
    echo nl2br(htmlspecialchars($result['choices'][0]['message']['content']));
} else {
    echo "Não foi possível obter uma sugestão no momento.";
}
?>