<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="equipos.css">
    <title>Document</title>
</head>
<body>
     <div class="container mx-auto p-4">
        <!-- Botón para crear nuevo equipo -->
        <button onclick="mostrarModalCrear()" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">
            <i class="fas fa-users mr-2"></i>Crear Nuevo Equipo
        </button>
        <!-- Listado de Equipos -->
        <div id="lista-equipos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
    
        <!-- Modal Crear Equipo -->
        <div id="modal-crear" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg w-96">
                <h3 class="text-xl font-bold mb-4">Nuevo Equipo</h3>
                <input type="text" id="nombre-equipo" placeholder="Nombre del equipo" class="w-full mb-2 p-2 border rounded">
                <textarea id="desc-equipo" placeholder="Descripción..." class="w-full mb-4 p-2 border rounded"></textarea>
                <div class="flex justify-end gap-2">
                    <button onclick="ocultarModalCrear()" class="bg-gray-500 text-white px-4 py-2 rounded">Cancelar</button>
                    <button onclick="crearEquipo()" class="bg-blue-500 text-white px-4 py-2 rounded">Crear</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../js/equipos.js"></script>    
</body>
</html>