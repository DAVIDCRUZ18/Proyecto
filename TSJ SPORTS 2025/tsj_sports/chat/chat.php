<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat en Tiempo Real</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="estilo-chat.css">
</head>
<body>
    <!-- Incluir el menú -->
    <script>
        fetch('../index.html')
            .then(response => response.text())
            .then(data => {
                document.body.innerHTML = data + document.body.innerHTML;
            });
    </script>

    <div class="chat-container">
        <div class="chat-header">
            <h2>Chat Global</h2>
            <div class="online-status">
                <span class="dot"></span>
                <span>Conectados: <span id="usuarios-conectados">0</span></span>
            </div>
        </div>
        
        <div class="chat-mensajes" id="mensajes-container"></div>
        
        <form id="form-chat" class="chat-form">
            <input type="text" id="usuario" placeholder="Tu nombre" required>
            <div class="input-group">
                <input type="text" id="mensaje" placeholder="Escribe tu mensaje..." required>
                <button type="submit">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>

    <script src="chat.js"></script>
</body>
</html>



