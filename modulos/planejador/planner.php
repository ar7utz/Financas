<?php
session_start();
include_once '../../assets/bd/conexao.php'
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <title>Planejador</title>

</head>
<body class="min-h-screen overflow-hidden">
    <?php include_once('../../assets/templates/navbar.php') ?>

    <h1 class="m-4 text-2xl font-bold mb-4 text-center">Planejador Financeiro</h1>

    <div class="flex items-center justify-center h-96">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">

            <form id="planejadorForm" method="POST" action="salvar_planejador.php">
                <!-- Nome/Razão -->
                <div class="step active">
                    <label for="nome" class="block text-gray-700 font-medium mb-2">Nome/Razão (Qual a sua meta?)</label>
                    <input type="text" id="razao" name="razao" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Preço da Meta -->
                <div class="step hidden">
                    <label for="preco_meta" class="block text-gray-700 font-medium mb-2">Preço da meta ($)</label>
                    <input type="number" id="preco_meta" name="preco_meta" step="0.01" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Quanto Tenho -->
                <div class="step hidden">
                    <label for="quanto_tenho" class="block text-gray-700 font-medium mb-2">Quanto eu tenho ($)</label>
                    <input type="number" id="capital" name="capital" step="0.01" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Tempo para Pagar -->
                <div class="step hidden">
                    <label for="tempo_pagar" class="block text-gray-700 font-medium mb-2">Quanto tempo quero pagar (meses)</label>
                    <input type="number" id="quanto_tempo_quero_pagar" name="quanto_tempo_quero_pagar" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Quanto Quero Pagar por Mês -->
                <div class="step hidden">
                    <label for="valor_mensal" class="block text-gray-700 font-medium mb-2">Quanto quero pagar por mês ($)</label>
                    <input type="number" id="quanto_quero_pagar_mes" name="quanto_quero_pagar_mes" step="0.01" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Botões de navegação -->
                <div class="flex justify-between mt-4">
                    <button type="button" id="prevBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hidden">Anterior</button>
                    <button type="button" id="nextBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Próximo</button>
                </div>
                <!-- Botão de enviar no último passo -->
                <div class="mt-4 hidden" id="submitBtnWrapper">
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg w-full">Enviar Planejamento</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const steps = document.querySelectorAll(".step");
        const nextBtn = document.getElementById("nextBtn");
        const prevBtn = document.getElementById("prevBtn");
        const submitBtnWrapper = document.getElementById("submitBtnWrapper");
        let currentStep = 0;

        function showStep(index) {
            steps.forEach((step, i) => {
                step.classList.toggle("hidden", i !== index);
                step.classList.toggle("active", i === index);
            });
            prevBtn.classList.toggle("hidden", index === 0);
            nextBtn.classList.toggle("hidden", index === steps.length - 1);
            submitBtnWrapper.classList.toggle("hidden", index !== steps.length - 1);
        }

        nextBtn.addEventListener("click", () => {
            if (currentStep < steps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        });

        prevBtn.addEventListener("click", () => {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });

        showStep(currentStep);
    </script>

    <script> //toastify 
        document.getElementById('notifyBtn').addEventListener('click', function() {
            Toastify({
                text: "Esta é uma notificação com Toastify!",
                duration: 3000, //duração em milissegundos
                close: true, //botão de fechar
                gravity: "bottom", //"top" ou "bottom"
                position: "right", 
                backgroundColor: "#1133A6",
            }).showToast();
        });
    </script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</body>
</html>