<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Petrona:ital,wght@0,100..900;1,100..900&family=Titan+One&family=Volkhov:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <script src="../../node_modules/toastify-js/src/toastify.css"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <title>Descubra seu perfil financeiro</title>
</head>
<body class="bg-gray-100 min-h-screen font-OpenSans">
    <?php require_once '../../assets/templates/navbar.php' ?>

    <div class="font-OpenSans max-w-3xl mx-auto mt-10 mb-10 bg-white rounded-xl shadow-lg p-8">
        <div class="flex flex-col items-center text-center">
            <h1 class="text-3xl font-bold mb-2 text-blue-700">Descubra seu Perfil Financeiro</h1>
            <p class="text-gray-600 mb-6 max-w-2xl">
                O perfil financeiro é uma análise do seu comportamento diante de investimentos, riscos e objetivos. 
                Conhecer seu perfil é fundamental para tomar decisões mais seguras, montar uma carteira adequada e receber sugestões de investimentos alinhadas com suas necessidades e expectativas.
            </p>
            <div class="w-full flex flex-col items-center mb-8">
                <img src="../../assets/img/perfil_investidor_ilustracao.svg" alt="Ilustração perfil financeiro" class="w-40 mb-4">
                <p class="text-gray-700 text-base max-w-xl">
                    Ao identificar seu perfil, você evita escolhas impulsivas, reduz as chances de prejuízo e aumenta as oportunidades de alcançar seus objetivos financeiros. 
                    O perfil é a base para recomendações personalizadas, respeitando seu apetite ao risco, horizonte de investimento e liquidez desejada.
                </p>
            </div>
        </div>

        <h2 class="text-2xl font-semibold text-blue-600 mb-4 mt-8 text-center">Perfis de Investidor</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Conservador -->
            <div class="bg-gray-50 rounded-lg p-6 shadow text-center flex flex-col items-center">
                <h3 class="text-lg font-bold text-green-700 mb-2">Conservador</h3>
                <p class="text-gray-700 mb-2">
                    Busca segurança acima de tudo, com preservação do capital e aversão a perdas.
                </p>
                <ul class="text-gray-600 text-sm mb-2">
                    <li>Prefere investimentos previsíveis, como renda fixa (Tesouro Direto, CDB, LCI/LCA)</li>
                    <li>Horizonte de curto prazo e alta liquidez</li>
                </ul>
                <span class="text-xs text-gray-400">Fontes: Estadão E-Investidor, Ativa Investimentos, André Bona</span>
            </div>
            <!-- Moderado -->
            <div class="bg-gray-50 rounded-lg p-6 shadow text-center flex flex-col items-center">
                <h3 class="text-lg font-bold text-yellow-700 mb-2">Moderado</h3>
                <p class="text-gray-700 mb-2">
                    Aceita algum risco para obter retorno acima da média.
                </p>
                <ul class="text-gray-600 text-sm mb-2">
                    <li>Divide a carteira entre renda fixa e renda variável (ações, FIIs, debêntures, multimercados)</li>
                    <li>Horizonte de médio prazo</li>
                </ul>
                <span class="text-xs text-gray-400">Fontes: Estadão E-Investidor, Ativa Investimentos, SpaceMoney Investimentos</span>
            </div>
            <!-- Agressivo -->
            <div class="bg-gray-50 rounded-lg p-6 shadow text-center flex flex-col items-center">
                <h3 class="text-lg font-bold text-red-700 mb-2">Agressivo (ou Arrojado)</h3>
                <p class="text-gray-700 mb-2">
                    Tolerância elevada a riscos e volatilidade, em busca de retornos mais expressivos.
                </p>
                <ul class="text-gray-600 text-sm mb-2">
                    <li>Investe majoritariamente em renda variável (ações de crescimento, criptomoedas, derivativos)</li>
                    <li>Mantém pequena reserva em renda fixa</li>
                    <li>Visão de longo prazo (4–5 anos ou mais)</li>
                </ul>
                <span class="text-xs text-gray-400">Fontes: Estadão E-Investidor, Ativa Investimentos, SpaceMoney Investimentos</span>
            </div>
        </div>

        <div class="mt-10 text-center">
            <a href="./form_perfil.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold shadow hover:bg-blue-700 transition">
                Faça seu teste de perfil financeiro
            </a>
        </div>
    </div>   
</body>
</html>