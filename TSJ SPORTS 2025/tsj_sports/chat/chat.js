// Obtener usuario desde localStorage  
let usuarioActual = localStorage.getItem('usuario');  
document.getElementById('usuario').value = usuarioActual;  


// Enviar mensaje
document.getElementById('form-chat').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    usuarioActual = document.getElementById('usuario').value;
    const mensaje = document.getElementById('mensaje').value;
    
    if(!usuarioActual || !mensaje) return;
    
    try {
        await fetch('../php/enviar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `usuario=${encodeURIComponent(usuarioActual)}&mensaje=${encodeURIComponent(mensaje)}`
        });
        
        document.getElementById('mensaje').value = '';
        cargarMensajes();
    } catch (error) {
        console.error('Error:', error);
    }
});

// Obtener mensajes cada segundo
setInterval(cargarMensajes, 1000);

async function cargarMensajes() {
    try {
        const response = await fetch('../php/obtener.php');
        const mensajes = await response.json();
        
        const contenedor = document.getElementById('mensajes-container');
        contenedor.innerHTML = '';
        
        mensajes.forEach(msg => {
            const mensajeDiv = document.createElement('div');
            mensajeDiv.className = `mensaje ${msg.usuario === usuarioActual ? 'propio' : 'otro'}`;
            mensajeDiv.innerHTML = `
                <strong>${msg.usuario}</strong>
                <p>${msg.mensaje}</p>
                <small>${new Date(msg.fecha).toLocaleTimeString()}</small>
            `;
            contenedor.appendChild(mensajeDiv);
        });
        
        contenedor.scrollTop = contenedor.scrollHeight;
    } catch (error) {
        console.error('Error:', error);
    }
}

// Actualizar usuarios conectados
setInterval(async () => {
    try {
        const response = await fetch('../php/obtener.php?accion=conectados');
        const data = await response.json();
        document.getElementById('usuarios-conectados').textContent = data.conectados;
    } catch (error) {
        console.error('Error:', error);
    }
}, 3000);
