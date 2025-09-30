<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Asistente Virtual - Vertex Chat</title>
<style>
body { font-family: Arial, sans-serif; background: #f0f2f5; margin:0; padding:0; }
.container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 20px; }
form { display:flex; justify-content:center; margin-bottom: 20px; }
input[type=text] { flex:1; padding:12px; border-radius:6px; border:1px solid #ccc; margin-right:10px; }
button { padding:12px 25px; border-radius:6px; border:none; background:#007bff; color:#fff; cursor:pointer; }
button:hover { background:#0056b3; }
.chat-box { max-height:500px; overflow-y:auto; padding:10px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9; }
.message { padding:10px 15px; margin:8px 0; border-radius:15px; max-width:70%; word-wrap:break-word; }
.user { background:#007bff; color:#fff; margin-left:auto; text-align:right; }
.assistant { background:#e2e2e2; color:#000; margin-right:auto; text-align:left; }
.icon { margin-right:5px; }
.ver-mas { color:#007bff; cursor:pointer; text-decoration:underline; font-size:13px; margin-top:5px; display:inline-block; }
</style>
</head>
<body>
<div class="container">
<h2>Asistente Virtual - Vertex Chat</h2>
<form id="chat-form">
<input type="text" name="query" placeholder="Escribe tu consulta..." required autofocus autocomplete="off">
<button type="submit">Enviar</button>
</form>
<div class="chat-box" id="chat-box"></div>
</div>

<script>
const chatBox = document.getElementById('chat-box');
const form = document.getElementById('chat-form');

function renderMessage(role, message) {
    const div = document.createElement('div');
    div.className = 'message ' + role;

    // Agregar iconos según rol
    const icon = document.createElement('span');
    icon.className = 'icon';
    icon.textContent = role === 'user' ? '🧑' : '🤖';
    div.appendChild(icon);

    const text = document.createElement('span');
    text.innerHTML = message.replace(/\n/g, '<br>');
    div.appendChild(text);

    // Botón ver más si mensaje largo
    if (message.length > 250 && role === 'assistant') {
        const verMas = document.createElement('span');
        verMas.className = 'ver-mas';
        verMas.textContent = 'Ver más';
        verMas.onclick = () => {
            text.innerHTML = message.replace(/\n/g, '<br>');
            verMas.style.display = 'none';
        };
        text.innerHTML = message.substring(0, 250).replace(/\n/g, '<br') + '... ';
        div.appendChild(verMas);
    }

    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const query = form.query.value.trim();
    if (!query) return;
    renderMessage('user', query);
    form.query.value = '';

    const res = await fetch('ajax_search.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({query})
    });
    const data = await res.json();
    renderMessage('assistant', data.reply || 'Error en la respuesta');
});
</script>
</body>
</html>
    