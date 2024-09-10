const saldoElement = document.getElementById('saldo');
const entradasElement = document.getElementById('entradas');
const saidasElement = document.getElementById('saidas');
const historicoList = document.getElementById('historico-list');
const descricaoInput = document.getElementById('descricao');
const valorInput = document.getElementById('valor');
const dataInput = document.getElementById('data');
const modalOverlay = document.getElementById('modal');
const toggleModalButton = document.getElementById('toggleModal');
const fecharModalButton = document.getElementById('fecharModal');

const transacoes = [];

let indiceEdicao = -1; 

function adicionarTransacao() {
    const descricao = descricaoInput.value.trim();
    const valor = valorInput.value.trim();
    const data = dataInput.value;

    if (descricao === '' || valor === '' || data === '') {
        alert('Preencha todos os campos corretamente.');
        return;
    }

    if (!/^(\d+([,.]\d{1,2})?)$/.test(valor)) {
        alert('O formato do valor é inválido. Use números e uma vírgula opcional seguida de até duas casas decimais.');
        return;
    }

    const valorFormatado = valor.replace(',', '.');
    const valorNumerico = parseFloat(valorFormatado);
    if (isNaN(valorNumerico)) {
        alert('O valor inserido não é válido.');
        return;
    }

    if (indiceEdicao !== -1) {
        const transacaoEditada = {
            descricao,
            valor,
            data,
        };
        transacoes[indiceEdicao] = transacaoEditada;
    } else {
        const transacao = {
            descricao,
            valor,
            data,
        };
        transacoes.push(transacao);
    }

    atualizarHistorico();
    atualizarBalanco();

    limparCampos();
    indiceEdicao = -1;
    fecharModal();
}



function atualizarHistorico(transacao) {
    const listItem = document.createElement('li');
    const valorPrefixo = transacao.valor < 0 ? '-' : '+';
    listItem.innerHTML = `
        <p>${transacao.descricao}</p>
        <p>${valorPrefixo} R$ ${Math.abs(transacao.valor).toFixed(2)}</p>
        <p class="data">${transacao.data}</p>
    `;

    historicoList.appendChild(listItem);
}

function fecharModal() {
    modalOverlay.style.display = 'none';
}

function abrirModal() {
    modalOverlay.style.display = 'flex';
}

function limparCampos() {
    descricaoInput.value = '';
    valorInput.value = '';
    dataInput.value = '';
}

toggleModalButton.addEventListener('click', abrirModal);
fecharModalButton.addEventListener('click', fecharModal);

function criarItemHistorico(transacao) {
    const listItem = document.createElement('li');
    const valorPrefixo = transacao.valor < 0 ? '-' : '+';
    listItem.innerHTML = `
        <p>${transacao.descricao}</p>
        <p>${valorPrefixo} R$ ${Math.abs(transacao.valor).toFixed(2)}</p>
        <p class="data">${transacao.data}</p>
        <button class="editar" data-id="${transacoes.indexOf(transacao)}">Editar</button>
        <button class="excluir" data-id="${transacoes.indexOf(transacao)}">Excluir</button>
        `;

        listItem.querySelector('.editar').addEventListener('click', function() {
            const transacaoId = this.getAttribute('data-id');
            editarTransacao(transacaoId);
        });

    return listItem;
}

function atualizarHistorico() {
    historicoList.innerHTML = '';
    transacoes.forEach(transacao => {
        const listItem = criarItemHistorico(transacao);
        historicoList.appendChild(listItem);
    });
}

function removerTransacao(index) {
    if (index >= 0 && index < transacoes.length) {
        transacoes.splice(index, 1);
        atualizarBalanco();
        atualizarHistorico();
    }
}

function confirmarExclusao(transacaoId) {
    const transacaoIndex = parseInt(transacaoId);

    console.log('ID da transação:', transacaoId);

    if (!isNaN(transacaoIndex) && transacaoIndex >= 0) {
        const modalConfirmarExclusao = document.getElementById('modalConfirmarExclusao');
        const confirmarExcluirNotaButton = document.getElementById('confirmarExcluirNota');
        const cancelarExcluirNotaButton = document.getElementById('cancelarExcluirNota');

        modalConfirmarExclusao.style.display = 'flex';

        confirmarExcluirNotaButton.onclick = function () {
            const xhr = new XMLHttpRequest();
            const url = '../../modulos/transacoes/excluir_transação.php?id=' + transacaoId;

            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        removerTransacao(transacaoIndex);
                        location.reload();
                    } else {
                        console.error('Erro ao excluir a transação:', xhr.status);
                    }
                }
            };

            xhr.open('GET', url);
            xhr.send();

            modalConfirmarExclusao.style.display = 'none';
        };

        cancelarExcluirNotaButton.onclick = function () {
            modalConfirmarExclusao.style.display = 'none';
        };
    } else {
        console.error('Transação não encontrada:', transacaoId);
    }
}

document.querySelectorAll('.excluir').forEach(button => {
    button.addEventListener('click', function() {
        const transacaoId = this.getAttribute('data-id');
        confirmarExclusao(transacaoId);
    });
});

function atualizarHistorico() {
    historicoList.innerHTML = '';
    transacoes.forEach(transacao => {
        const listItem = criarItemHistorico(transacao);
        historicoList.appendChild(listItem);
    });

    console.log('Transações:', transacoes);
}