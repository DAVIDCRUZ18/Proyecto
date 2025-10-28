<?php
session_start();
// Verificar que haya sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /TSJ SPORTS 2025/tsj_sports/inicio.html');
    exit;
}
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/header.php";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos - TSJ SPORTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-top: 80px;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .modal-backdrop {
            backdrop-filter: blur(5px);
        }

        .team-card {
            transition: all 0.3s ease;
        }

        .team-card:hover {
            transform: translateY(-5px);
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .modal {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-5xl font-bold text-white mb-3 flex items-center gap-3">
                <i class="fas fa-users"></i> Equipos
            </h1>
            <p class="text-white/90 text-lg">Crea tu equipo o únete a uno existente para empezar a competir</p>
        </div>
        
        <!-- Botón crear equipo -->
        <button onclick="mostrarModalCrear()" 
                class="btn-primary text-white px-8 py-4 rounded-xl mb-8 shadow-2xl font-semibold text-lg flex items-center gap-2">
            <i class="fas fa-plus-circle"></i>
            Crear Nuevo Equipo
        </button>
        
        <!-- Listado de Equipos -->
        <div id="lista-equipos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Loading spinner -->
            <div class="col-span-full text-center p-12">
                <div class="loading-spinner"></div>
                <p class="text-white mt-4 text-lg">Cargando equipos...</p>
            </div>
        </div>
    </div>

    <!-- Modal Crear Equipo -->
    <div id="modal-crear" class="hidden fixed inset-0 bg-black/60 modal-backdrop flex items-center justify-center z-50 p-4">
        <div class="modal bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 rounded-t-2xl">
                <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-trophy"></i>
                    Crear Nuevo Equipo
                </h3>
            </div>
            
            <div class="p-6">
                <form id="form-crear-equipo" onsubmit="event.preventDefault(); crearEquipo();">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-shield-alt text-blue-500"></i> Nombre del Equipo *
                        </label>
                        <input type="text" 
                               id="nombre-equipo" 
                               placeholder="Ej: Los Tigres FC" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                               required
                               minlength="3"
                               maxlength="100">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-align-left text-blue-500"></i> Descripción
                        </label>
                        <textarea id="desc-equipo" 
                                  placeholder="Describe tu equipo..." 
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition resize-none"
                                  rows="4"
                                  maxlength="500"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Opcional - Máximo 500 caracteres</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-map-marker-alt text-blue-500"></i> Ciudad
                        </label>
                        <input type="text" 
                               id="ciudad-equipo" 
                               placeholder="Ej: Bogotá" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                               maxlength="100">
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" 
                                onclick="ocultarModalCrear()" 
                                class="flex-1 bg-gray-400 hover:bg-gray-500 text-white px-4 py-3 rounded-lg font-semibold transition">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" 
                                id="btn-crear"
                                class="flex-1 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-4 py-3 rounded-lg font-semibold transition">
                            <i class="fas fa-check"></i> Crear Equipo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Cargar equipos al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            cargarEquipos();
        });

        // Cerrar modal con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                ocultarModalCrear();
            }
        });

        // Cerrar modal al hacer clic fuera
        document.getElementById('modal-crear')?.addEventListener('click', (e) => {
            if (e.target.id === 'modal-crear') {
                ocultarModalCrear();
            }
        });

        // Cargar lista de equipos
        async function cargarEquipos() {
            try {
                const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/equipos/listar_equipos.php');
                const data = await response.json();
                
                if (data.success) {
                    mostrarEquipos(data.equipos);
                } else {
                    mostrarError('Error al cargar equipos: ' + data.message);
                }
            } catch (error) {
                console.error('Error al cargar equipos:', error);
                mostrarError('Error de conexión al cargar equipos');
            }
        }

        // Mostrar equipos en el DOM
        function mostrarEquipos(equipos) {
            const container = document.getElementById('lista-equipos');
            
            if (equipos.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center p-12 bg-white/10 backdrop-blur-sm rounded-2xl">
                        <i class="fas fa-users text-6xl text-white/50 mb-4"></i>
                        <p class="text-white text-xl font-semibold mb-2">No hay equipos creados aún</p>
                        <p class="text-white/70">¡Sé el primero en crear un equipo!</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = equipos.map(equipo => `
                <div class="team-card bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6">
                        <div class="flex items-center gap-4">
                            <img src="${equipo.escudo}" 
                                 alt="${equipo.nombre_equipo}" 
                                 class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold text-white mb-1">${equipo.nombre_equipo}</h3>
                                <p class="text-white/90 text-sm flex items-center gap-1">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    ${equipo.ciudad || 'Sin ubicación'}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2 min-h-[40px]">
                            ${equipo.descripcion}
                        </p>
                        
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-4 pb-4 border-b">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-user-shield text-blue-500"></i> 
                                <strong>${equipo.capitan_nombre}</strong>
                            </span>
                            <span class="flex items-center gap-2 bg-blue-50 px-3 py-1 rounded-full">
                                <i class="fas fa-users text-blue-500"></i> 
                                <strong>${equipo.total_jugadores}</strong>
                            </span>
                        </div>
                        
                        <div class="flex gap-2">
                            <button onclick="verDetalleEquipo(${equipo.id_equipo})" 
                                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                            ${!equipo.es_miembro ? `
                                <button onclick="unirseEquipo(${equipo.id_equipo})" 
                                        class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                                    <i class="fas fa-plus"></i> Unirse
                                </button>
                            ` : `
                                ${!equipo.soy_capitan ? `
                                    <button onclick="salirEquipo(${equipo.id_equipo})" 
                                            class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                                        <i class="fas fa-sign-out-alt"></i> Salir
                                    </button>
                                ` : `
                                    <div class="flex-1 bg-yellow-500 text-white px-4 py-3 rounded-lg font-semibold text-center flex items-center justify-center gap-2">
                                        <i class="fas fa-crown"></i> Capitán
                                    </div>
                                `}
                            `}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Mostrar error
        function mostrarError(mensaje) {
            const container = document.getElementById('lista-equipos');
            container.innerHTML = `
                <div class="col-span-full text-center p-12 bg-red-500/20 backdrop-blur-sm rounded-2xl border-2 border-red-500">
                    <i class="fas fa-exclamation-triangle text-5xl text-red-200 mb-4"></i>
                    <p class="text-white text-lg font-semibold">${mensaje}</p>
                    <button onclick="cargarEquipos()" 
                            class="mt-4 bg-white text-red-500 px-6 py-2 rounded-lg font-semibold hover:bg-red-50 transition">
                        Reintentar
                    </button>
                </div>
            `;
        }

        // Mostrar modal de crear equipo
        function mostrarModalCrear() {
            document.getElementById('modal-crear').classList.remove('hidden');
            document.getElementById('nombre-equipo').focus();
        }

        // Ocultar modal de crear equipo
        function ocultarModalCrear() {
            document.getElementById('modal-crear').classList.add('hidden');
            document.getElementById('form-crear-equipo').reset();
        }

        // Crear equipo
        async function crearEquipo() {
            const nombre = document.getElementById('nombre-equipo').value.trim();
            const descripcion = document.getElementById('desc-equipo').value.trim();
            const ciudad = document.getElementById('ciudad-equipo').value.trim();
            const btn = document.getElementById('btn-crear');
            
            if (!nombre) {
                alert('El nombre del equipo es requerido');
                return;
            }
            
            if (nombre.length < 3) {
                alert('El nombre debe tener al menos 3 caracteres');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';
            
            try {
                const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/equipos/crear_equipo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nombre_equipo: nombre,
                        descripcion: descripcion,
                        ciudad: ciudad
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('¡Equipo creado exitosamente!');
                    ocultarModalCrear();
                    cargarEquipos();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al crear el equipo');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Crear Equipo';
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
                    alert('¡Te has unido al equipo exitosamente!');
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

        // Ver detalle del equipo
        function verDetalleEquipo(idEquipo) {
            window.location.href = `detalle_equipo.php?id=${idEquipo}`;
        }
    </script>
</body>
</html>