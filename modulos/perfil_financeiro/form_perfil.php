<?php
session_start();
require_once '../../assets/bd/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$usuario_id = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link rel="shortcut icon" href="../../assets/logo/cube_logo_no_background.ico" type="image/x-icon">
    <title>Descubra seu perfil financeiro</title>
    <script src="https://unpkg.com/scrollreveal"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Petrona:ital,wght@0,100..900;1,100..900&family=Titan+One&family=Volkhov:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>
    <?php require_once '../../assets/templates/navbar.php' ?>

    <div class="max-w-3xl mx-auto mt-10 bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6">Descubra seu perfil financeiro</h1>
        <p class="mb-4">Responda às perguntas abaixo para descobrir seu perfil financeiro e receber recomendações personalizadas.</p>
        <!-- Barra de progresso -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-1">
                <span id="progressText" class="text-gray-600 text-lg"></span>
            </div>
            <div class="w-full bg-gray-100 rounded h-2 overflow-hidden">
                <div id="progressBar" class="bg-tollens h-2 transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
        <form method="POST" action="./pontuação_perfil.php" id="perfilFinanceiroForm" class="bg-white p-6 rounded-lg shadow-md">
            <div class="step">
                Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                Porro similique officiis, praesentium illum error vero animi qui veritatis accusantium,
                a sunt sequi deserunt numquam pariatur ratione ipsa incidunt rerum ut!
            </div>

            <!-- Passo 1 -->
            <div class="step">
                <label for="pergunta1" class="block text-gray-700 text-sm font-bold mb-2">1. Qual é a sua principal prioridade financeira?</label>
                <select id="pergunta1" name="pergunta1" class="form-select block w-full mt-1" required>
                    <option disabled selected value="">Selecione</option>
                    <option value="economizar">Economizar para o futuro</option>
                    <option value="investir">Investir em ativos</option>
                    <option value="gastar">Gastar em experiências</option>
                    <option value="pagarDividas">Pagar dívidas</option>
                    <option value="seguranca">Segurança financeira</option>
                </select>
            </div>

            <!-- Passo 2 -->
            <div class="step hidden">
                <label for="pergunta2" class="block text-gray-700 text-sm font-bold mb-2">2. Como você lida com investimentos?</label>
                <select id="pergunta2" name="pergunta2" class="form-select block w-full mt-1" required>
                    <option disabled selected value="">Selecione</option>
                    <option value="conservador">Prefiro investimentos conservadores</option>
                    <option value="moderado">Estou disposto a correr riscos moderados</option>
                    <option value="arrojado">Gosto de investimentos arrojados</option>
                    <option value="especulativo">Faço investimentos especulativos</option>
                    <option value="naoInvisto">Não invisto</option>
                </select>
            </div>

            <!-- Passo 3 -->
            <div class="step hidden">
                <label for="pergunta3" class="block text-gray-700 text-sm font-bold mb-2">3. Qual é o seu nível de conhecimento sobre finanças pessoais?</label>
                <select id="pergunta3" name="pergunta3" class="form-select block w-full mt-1" required>
                    <option disabled selected value="">Selecione</option>
                    <option value="iniciante">Sou iniciante</option>
                    <option value="intermediario">Tenho conhecimento intermediário</option>
                    <option value="avancado">Sou avançado</option>
                    <option value="especialista">Sou especialista</option>
                    <option value="naoSei">Não sei</option>
                </select>
            </div>

            <!-- Passo 4 -->
            <div class="step hidden">
                <label for="pergunta4" class="block text-gray-700 text-sm font-bold mb-2">4. Como você lida com dívidas?</label>
                <select id="pergunta4" name="pergunta4" class="form-select block w-full mt-1" required>
                    <option disabled selected value="">Selecione</option>
                    <option value="evito">Evito dívidas</option>
                    <option value="gerencio">Gerencio minhas dívidas</option>
                    <option value="aceito">Aceito dívidas para investimentos</option>
                    <option value="naoMeImporto">Não me importo com dívidas</option>
                    <option value="naoTenho">Não tenho dívidas</option>
                </select>
            </div>

            <!-- Passo 5 -->
            <div class="step hidden">
                <label for="pergunta5" class="block text-gray-700 text-sm font-bold mb-2">5. Qual é a sua abordagem em relação à aposentadoria?</label>
                <select id="pergunta5" name="pergunta5" class="form-select block w-full mt-1" required>
                    <option disabled selected value="">Selecione</option>
                    <option value="planejo">Planejo minha aposentadoria</option>
                    <option value="naoPlanejo">Não planejo minha aposentadoria</option>
                    <option value="dependo">Dependo da previdência social</option>
                    <option value="naoMeImporto">Não me importo com aposentadoria</option>
                    <option value="jaAposentei">Já estou aposentado</option>
                </select>
            </div>
            <!-- Botões de navegação -->
            <div class="flex justify-between mt-4">
                <button type="button" id="prevBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hidden">Anterior</button>
                <button type="button" id="nextBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Próximo</button>
            </div>
            <!-- Botão de enviar no último passo -->
            <div class="mt-4 hidden" id="submitBtnWrapper">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg w-full">Descobrir Perfil</button>
            </div>
        </form>
        <div id="resultado" class="mt-6 bg-green-100 p-4 rounded-lg shadow-md hidden">
            <h2 class="text-xl font-bold mb-2 text-center">Seu Perfil Financeiro</h2>
            <p class="text-gray-700" id="perfilResultado">
            </p>
        </div>
    </div>

    <script>
        const steps = document.querySelectorAll(".step");
        const nextBtn = document.getElementById("nextBtn");
        const prevBtn = document.getElementById("prevBtn");
        const submitBtnWrapper = document.getElementById("submitBtnWrapper");
        const progressText = document.getElementById("progressText");
        const progressBar = document.getElementById("progressBar");
        let currentStep = 0;

        // Inicializa apenas o primeiro passo visível
        steps.forEach((step, i) => {
            if (i === 0) {
                step.classList.remove("hidden");
            } else {
                step.classList.add("hidden");
            }
        });

        // Função para mostrar passo com animação ScrollReveal
        function showStep(index) {
            steps.forEach((step, i) => {
                if (i === index) {
                    step.classList.remove("hidden");
                    ScrollReveal().reveal(step, {
                        duration: 500,
                        distance: '40px',
                        easing: 'ease',
                        origin: 'right',
                        reset: false,
                        opacity: 0
                    });
                } else {
                    step.classList.add("hidden");
                }
            });
            prevBtn.classList.toggle("hidden", index === 0);
            nextBtn.classList.toggle("hidden", index === steps.length - 1);
            submitBtnWrapper.classList.toggle("hidden", index !== steps.length - 1);

            // Atualiza a barra de progresso SOMENTE se não for o texto inicial (lorem)
            if (index === 0) {
                progressBar.style.width = '0%';
                progressText.innerText = '';
            } else {
                // Total de perguntas (desconsiderando o texto inicial)
                const totalPerguntas = steps.length - 1;
                progressBar.style.width = `${(index / totalPerguntas) * 100}%`;
                progressText.innerText = `Pergunta ${index} de ${totalPerguntas}`;
            }
        }

        nextBtn.addEventListener("click", () => {
            // Validação simples: exige resposta antes de avançar
            const select = steps[currentStep].querySelector('select');
            if (select && !select.value) {
                select.classList.add('border-red-500');
                return;
            } else if (select) {
                select.classList.remove('border-red-500');
            }
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

        // Envio AJAX para exibir resultado sem recarregar a página
        document.getElementById('perfilFinanceiroForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('pontuação_perfil.php', {
                method: 'POST',
                body: formData
            })
            .then(resp => resp.json())
            .then(data => {
                // Esconde o formulário de perguntas
                document.getElementById('perfilFinanceiroForm').classList.add('hidden');
                // Mostra o resultado
                document.getElementById('resultado').classList.remove('hidden');
                if (data.status === "ok") {
                    document.getElementById('perfilResultado').innerHTML =
                        `<span class="font-bold">Seu perfil financeiro é:</span>
                         <span class="text-blue-700">${data.perfil}</span><br>
                         <span class="text-sm text-gray-500">Pontuação: ${data.pontos}</span>`;
                } else {
                    document.getElementById('perfilResultado').innerText = "Erro ao calcular perfil: " + (data.msg || "Tente novamente.");
                }
            })
            .catch(() => {
                document.getElementById('perfilFinanceiroForm').classList.add('hidden');
                document.getElementById('resultado').classList.remove('hidden');
                document.getElementById('perfilResultado').innerText = "Erro ao enviar respostas. Tente novamente.";
            });

            // Desabilita os botões após envio
            prevBtn.disabled = true;
            nextBtn.disabled = true;
            prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
            nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
        });
    </script>
</body>
</html>