<?php
require_once '../../assets/bd/conexao.php';
session_start();



?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    <title>Planilha Financeira - Finstash</title>
</head>
<body>
    <?php include_once '../../assets/templates/navbar.php'; ?>

    <div class="flex w-10/12 m-4">
        <div>
            <button onclick="exportToExcel()" class="bg-blue-500 text-white p-2 rounded mb-4">Exportar para Excel</button>
        </div>
        <div id="grid-container" class="grid grid-cols-12 gap-1">
            <?php for($row = 1; $row <= 12; $row++): ?>
                <?php for($col = 1; $col <= 12; $col++): ?>
                    <input 
                        type="text" 
                        class="border border-gray-300 p-2 text-center" 
                        data-row="<?php echo $row; ?>" 
                        data-col="<?php echo $col; ?>"
                        placeholder="(<?php echo $row; ?>,<?php echo $col; ?>)"
                    >
                <?php endfor; ?>
            <?php endfor; ?>
        </div>
    </div>

    <script>
    function exportToExcel() {
        const gridContainer = document.getElementById('grid-container');
        const inputs = gridContainer.querySelectorAll('input');
        
        // Criar array de dados para o Excel
        const data = [];
        
        // Organizar dados por linhas
        for(let row = 1; row <= 12; row++) {
            const rowData = [];
            for(let col = 1; col <= 12; col++) {
                const cell = document.querySelector(`input[data-row="${row}"][data-col="${col}"]`);
                rowData.push(cell.value || '');
            }
            data.push(rowData);
        }

        // Criar planilha
        const worksheet = XLSX.utils.aoa_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Planilha");
        
        // Salvar arquivo
        XLSX.writeFile(workbook, "planilha_financeira.xlsx");
    }
    </script>
</body>
</html>