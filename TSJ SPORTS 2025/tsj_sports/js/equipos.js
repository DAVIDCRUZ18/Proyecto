// Cargar equipos al iniciar
document.addEventListener('DOMContentLoaded', () => {
    cargarEquipos();
});

// Cargar lista de equipos
async function cargarEquipos() {
    try {
        const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/equipos/listar_equipos.php');
        const data = await response.json();
        
        if (data.success) {
            mostrarEquipos(data.equipos);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error al cargar equipos:', error);
        alert('Error de conexión');
    }
}

// Mostrar equipos en el DOM
function mostrarEquipos(equipos) {
    const container = document.getElementById('lista-equipos');
    
    if (equipos.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center p-8">
                <p class="text-gray-500 text-lg">No hay equipos creados aún</p>
                <p class="text-gray-400 mt-2">¡Sé el primero en crear uno!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = equipos.map(equipo => `
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-4 mb-4">
                <img src="${equipo.escudo}" alt="${equipo.nombre_equipo}" class="w-16 h-16 rounded-full object-cover">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800">${equipo.nombre_equipo}</h3>
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt"></i> ${equipo.ciudad}
                    </p>
                </div>
            </div>
            
            <p class="text-gray-600 text-sm mb-4 line-clamp-2">${equipo.descripcion}</p>
            
            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                <span>
                    <i class="fas fa-user-shield"></i> 
                    Capitán: <strong>${equipo.capitan_nombre}</strong>
                </span>
                <span>
                    <i class="fas fa-users"></i> 
                    ${equipo.total_jugadores} jugadores
                </span>
            </div>
            
            <div class="flex gap-2">
                <button onclick="verDetalleEquipo(${equipo.id_equipo})" 
                        class="flex-1 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                    <i class="fas fa-eye"></i> Ver Detalles
                </button>
                ${!equipo.es_miembro ? `
                    <button onclick="unirseEquipo(${equipo.id_equipo})" 
                            class="flex-1 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                        <i class="fas fa-plus"></i> Unirse
                    </button>
                ` : `
                    ${!equipo.soy_capitan ? `
                        <button onclick="salirEquipo(${equipo.id_equipo})" 
                                class="flex-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </button>
                    ` : `
                        <span class="flex-1 bg-yellow-500 text-white px-4 py-2 rounded text-center">
                            <i class="fas fa-crown"></i> Tu equipo
                        </span>
                    `}
                `}
            </div>
        </div>
    `).join('');
}

// Mostrar modal de crear equipo
function mostrarModalCrear() {
    document.getElementById('modal-crear').classList.remove('hidden');
    document.getElementById('nombre-equipo').value = '';
    document.getElementById('desc-equipo').value = '';
}

// Ocultar modal de crear equipo
function ocultarModalCrear() {
    document.getElementById('modal-crear').classList.add('hidden');
}

// Crear equipo
async function crearEquipo() {
    const nombre = document.getElementById('nombre-equipo').value.trim();
    const descripcion = document.getElementById('desc-equipo').value.trim();
    
    if (!nombre) {
        alert('El nombre del equipo es requerido');
        return;
    }
    
    if (nombre.length < 3) {
        alert('El nombre debe tener al menos 3 caracteres');
        return;
    }
    
    try {
        const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/equipos/crear_equipo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nombre_equipo: nombre,
                descripcion: descripcion,
                ciudad: '' // Puedes agregar un campo para ciudad si quieres
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Equipo creado exitosamente');
            ocultarModalCrear();
            cargarEquipos();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

// Unirse a un equipo
async function unirseEquipo(idEquipo) {
    if (!confirm('¿Estás seguro de que quieres unirte a este equipo?')) {
        return;
    }
    
    try {
        const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/equipos/unirse_equipo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_equipo: idEquipo })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            cargarEquipos();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

// Salir de un equipo
async function salirEquipo(idEquipo) {
    if (!confirm('¿Estás seguro de que quieres salir de este equipo?')) {
        return;
    }
    
    try {
        const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/equipos/salir_equipo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_equipo: idEquipo })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            cargarEquipos();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

// Ver detalle del equipo (redireccionar o abrir modal)
function verDetalleEquipo(idEquipo) {
    // Puedes redirigir a una página de detalle
    window.location.href = `detalle_equipo.php?id=${idEquipo}`;
    
    // O abrir un modal con la información (implementar después)
    // mostrarModalDetalle(idEquipo);
}