import Toastify from 'toastify-js'
import "toastify-js/src/toastify.css"

document.getElementById('notifyBtn').addEventListener('click', function() {
    Toastify({
        text: "Esta é uma notificação com Toastify!",
        duration: 3000, // duração em milissegundos
        close: true, // botão de fechar
        gravity: "top", // "top" ou "bottom"
        position: "right", // "left", "right", ou "center"
        backgroundColor: "#1133A6", // Cor personalizada
    }).showToast();
});