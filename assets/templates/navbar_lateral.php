<link rel="stylesheet" href="/Financas/assets/css/output.css">
<div class="w-80 h-screen rounded-br-3xl">
    <div class="bg-red items-center justify-center text-center">
        <a href="../../modulos/dashboard/dashboard.php">
            <button id="cardbtn1teste" addGlassHoverEffect class="w-48 h-14 text-center mt-14 bg-ghostwhite rounded-md" style="box-shadow: 0 4px 32px 0 rgba(31, 38, 135, 0.15);">
                Dashboard
            </button>
        </a>

        <a href="../../modulos/planejador/page.php">
            <button id="cardbtn2teste" addGlassHoverEffect class="w-48 h-14 text-center mt-8 bg-ghostwhite rounded-md bg-white/20 backdrop-blur-md" style="box-shadow: 0 4px 32px 0 rgba(31, 38, 135, 0.15);">
                Planejador
            </button>
        </a>

        <a href="../../modulos/planilha/pagePS.php">
            <button id="cardbtn3teste" addGlassHoverEffect class="w-48 h-14 text-center mt-8 bg-ghostwhite rounded-md" style="box-shadow: 0 4px 32px 0 rgba(31, 38, 135, 0.15);">
                Planilha financeira
            </button>
        </a>
    </div>
</div>

    <script>
        // Função para aplicar efeito glassmorphism interativo
        function addGlassHoverEffect(divId, corBase = '#FFD600', corFundo = 'rgba(255,255,255,0.15)') {
            const div = document.getElementById(divId);
            if (!div) return;
        
            div.addEventListener('mousemove', function(e) {
                const rect = div.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                div.style.background = `
                    radial-gradient(circle at ${x}px ${y}px, ${corBase}55 0%, ${corBase}22 40%, ${corFundo} 100%)
                `;
                div.style.transition = 'background 0.2s';
            });
        
            div.addEventListener('mouseleave', function() {
                div.style.background = '';
            });
        }

        window.addEventListener('DOMContentLoaded', function() {
            addGlassHoverEffect('weatherGlass', '#1133A6', 'rgba(255,255,255,0.15)'); // tollens
            addGlassHoverEffect('clockGlass', '#1133A6', 'rgba(255,255,255,0.15)');
            addGlassHoverEffect('cardbtn1teste', '#1133A6', 'rgba(255,255,255,0.15)');
            addGlassHoverEffect('cardbtn2teste', '#1133A6', 'rgba(255,255,255,0.15)');
            addGlassHoverEffect('cardbtn3teste', '#1133A6', 'rgba(255,255,255,0.15)');
        });
    </script>