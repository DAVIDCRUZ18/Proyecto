document.addEventListener('DOMContentLoaded', cargarEquipos);

async function cargarEquipos() {
    try {
        const respuesta = await fetch('../php/equipos.php');
        const equipos = await respuesta.json();
        
        const contenedor = document.getElementById('lista-equipos');
        contenedor.innerHTML = equipos.map(equipo => `
            <div class="border rounded-lg p-4 shadow-md">
                <h3 class="text-lg font-bold mb-2">${equipo.nombre}</h3>
                <p class="text-gray-600 mb-2">${equipo.descripcion || 'Sin descripción'}</p>
                <div class="flex justify-between items-center text-sm">
                    <span>Miembros: ${equipo.miembros}</span>
                    <button onclick="unirseEquipo(${equipo.id})" 
                            class="bg-green-500 text-white px-3 py-1 rounded">
                        Unirse
                    </button>
                </div>
            </div>
        `).join('');
    } catch (error) {
        alert('Error cargando equipos');
    }
}

async function crearEquipo() {
    const nombre = document.getElementById('nombre-equipo').value;
    const desc = document.getElementById('desc-equipo').value;

    if (!nombre) {
        alert('El nombre es requerido');
        return;
    }

    try {
        const respuesta = await fetch('../php/equipos.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({nombre, descripcion: desc})
        });
        
        if (respuesta.ok) {
            ocultarModalCrear();
            cargarEquipos();
        } else {
            const error = await respuesta.json();
            alert(error.error || 'Error creando equipo');
        }
    } catch (error) {
        alert('Error de conexión');
    }
}

async function unirseEquipo(equipoId) {
    try {
        const respuesta = await fetch(`../php/miembros.php?equipo_id=${equipoId}`, {
            method: 'POST'
        });

        if (respuesta.ok) {
            alert('¡Te has unido al equipo!');
            cargarEquipos();
        } else {
            const error = await respuesta.json();
            alert(error.error || 'Error al unirse');
        }
    } catch (error) {
        alert('Error de conexión');
    }
}
