<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/header.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mapa de Canchas - TSJ SPORTS</title>
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="/TSJ SPORTS 2025/tsj_sports/css/stylesP.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden;
    }

    #map-container {
      position: relative;
      width: 100%;
      height: 100vh;
    }

    #map {
      width: 100%;
      height: 100%;
    }

    /* Panel lateral */
    .side-panel {
      position: absolute;
      top: 20px;
      right: 20px;
      width: 320px;
      max-height: calc(100vh - 40px);
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      z-index: 1000;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .panel-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px;
      text-align: center;
    }

    .panel-header h2 {
      font-size: 20px;
      margin-bottom: 5px;
    }

    .panel-header p {
      font-size: 13px;
      opacity: 0.9;
    }

    .panel-content {
      padding: 20px;
      overflow-y: auto;
      flex: 1;
    }

    /* Filtros */
    .filter-section {
      margin-bottom: 20px;
    }

    .filter-section h3 {
      font-size: 14px;
      color: #333;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .filter-buttons {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .filter-btn {
      flex: 1;
      min-width: 90px;
      padding: 8px 12px;
      border: 2px solid #e0e0e0;
      background: white;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s;
      font-size: 12px;
      text-align: center;
    }

    .filter-btn:hover {
      border-color: #667eea;
      background: #f8f9ff;
    }

    .filter-btn.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-color: #667eea;
    }

    /* Lista de canchas */
    .canchas-list {
      margin-top: 15px;
    }

    .cancha-item {
      background: #f8f9fa;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 10px;
      cursor: pointer;
      transition: all 0.3s;
      border-left: 4px solid #667eea;
    }

    .cancha-item:hover {
      transform: translateX(5px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .cancha-item h4 {
      font-size: 14px;
      color: #333;
      margin-bottom: 5px;
    }

    .cancha-item p {
      font-size: 12px;
      color: #666;
      margin: 3px 0;
    }

    .cancha-item .tipo-badge {
      display: inline-block;
      padding: 3px 8px;
      background: #667eea;
      color: white;
      border-radius: 4px;
      font-size: 10px;
      margin-top: 5px;
    }

    /* Cuadro de coordenadas mejorado */
    .coords-box {
      position: absolute;
      bottom: 20px;
      left: 20px;
      background: white;
      padding: 15px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      font-family: Arial, sans-serif;
      z-index: 1000;
      min-width: 250px;
    }

    .coords-box strong {
      color: #667eea;
      font-size: 13px;
    }

    .coords-box #coords {
      display: block;
      margin: 8px 0;
      padding: 8px;
      background: #f8f9fa;
      border-radius: 6px;
      font-family: 'Courier New', monospace;
      font-size: 12px;
      color: #333;
    }

    .coords-box button {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      font-weight: 600;
      transition: transform 0.2s;
    }

    .coords-box button:hover {
      transform: translateY(-2px);
    }

    .coords-box button:active {
      transform: translateY(0);
    }

    /* Botón de ubicación */
    .locate-btn {
      position: absolute;
      top: 90px;
      left: 10px;
      background: white;
      border: 2px solid rgba(0,0,0,0.2);
      border-radius: 8px;
      width: 40px;
      height: 40px;
      cursor: pointer;
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      transition: all 0.3s;
    }

    .locate-btn:hover {
      background: #667eea;
      color: white;
      border-color: #667eea;
    }

    /* Estilos personalizados de popup */
    .custom-popup .leaflet-popup-content-wrapper {
      border-radius: 12px;
      padding: 0;
      overflow: hidden;
    }

    .popup-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 15px;
      font-weight: 600;
    }

    .popup-body {
      padding: 15px;
    }

    .popup-body p {
      margin: 8px 0;
      font-size: 13px;
      color: #555;
    }

    .popup-body .icon {
      color: #667eea;
      margin-right: 8px;
    }

    .popup-link {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 16px;
      background: #667eea;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-size: 12px;
      transition: background 0.3s;
    }

    .popup-link:hover {
      background: #5568d3;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .side-panel {
        width: calc(100% - 40px);
        max-height: 50vh;
      }
      
      .coords-box {
        left: 50%;
        transform: translateX(-50%);
        bottom: 10px;
      }
    }
  </style>
</head>
<body>
  <div id="map-container">
    <div id="map"></div>
    
    <!-- Panel lateral -->
    <div class="side-panel">
      <div class="panel-header">
        <h2><i class="fas fa-futbol"></i> Canchas de Fútbol</h2>
        <p>Encuentra y reserva tu cancha</p>
      </div>
      <div class="panel-content">
        <!-- Filtros -->
        <div class="filter-section">
          <h3><i class="fas fa-filter"></i> Tipo de Cancha</h3>
          <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">Todas</button>
            <button class="filter-btn" data-filter="futbol5">Fútbol 5</button>
            <button class="filter-btn" data-filter="futbol7">Fútbol 7</button>
            <button class="filter-btn" data-filter="futbol11">Fútbol 11</button>
          </div>
        </div>

        <div class="filter-section">
          <h3><i class="fas fa-layer-group"></i> Superficie</h3>
          <div class="filter-buttons">
            <button class="filter-btn active" data-filter-surface="all">Todas</button>
            <button class="filter-btn" data-filter-surface="sintetica">Sintética</button>
            <button class="filter-btn" data-filter-surface="natural">Natural</button>
          </div>
        </div>

        <!-- Lista de canchas -->
        <div class="canchas-list" id="canchasList">
          <p style="text-align: center; color: #999;">Cargando canchas...</p>
        </div>
      </div>
    </div>

    <!-- Cuadro de coordenadas -->
    <div class="coords-box">
      <strong><i class="fas fa-map-marker-alt"></i> Coordenadas:</strong>
      <span id="coords">Haz clic en el mapa</span>
      <button id="copyBtn"><i class="fas fa-copy"></i> Copiar Coordenadas</button>
    </div>

    <!-- Botón de ubicación -->
    <button class="locate-btn" id="locateBtn" title="Mi ubicación">
      <i class="fas fa-location-arrow"></i>
    </button>
  </div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
  <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
  
  <script>
    // Datos de ejemplo de canchas (reemplazar con datos de PHP)
    const canchasData = [
      { id: 1, nombre: "Cancha El Dorado", lat: 4.7110, lng: -74.0821, tipo: "futbol5", superficie: "sintetica", direccion: "Calle 26 #68D-35" },
      { id: 2, nombre: "Parque Simón Bolívar", lat: 4.6597, lng: -74.0937, tipo: "futbol7", superficie: "natural", direccion: "Av. Calle 63 #48-66" },
      { id: 3, nombre: "Centro Deportivo Salitre", lat: 4.6570, lng: -74.0995, tipo: "futbol11", superficie: "sintetica", direccion: "Av. 68 #56-25" },
      { id: 4, nombre: "Cancha La Florida", lat: 4.6871, lng: -74.0556, tipo: "futbol5", superficie: "sintetica", direccion: "Carrera 7 #145-20" },
      { id: 5, nombre: "Complejo Tunal", lat: 4.5739, lng: -74.1271, tipo: "futbol7", superficie: "natural", direccion: "Av. Boyacá #47B-25 Sur" }
    ];

    // Inicializar mapa con mejor vista
    const map = L.map('map', {
      zoomControl: false
    }).setView([4.6871, -74.0721], 12);

    // Control de zoom personalizado
    L.control.zoom({
      position: 'topleft'
    }).addTo(map);

    // Capa base con CartoDB Positron (más limpia y moderna)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      attribution: '© OpenStreetMap contributors © CARTO',
      maxZoom: 19
    }).addTo(map);

    // Iconos personalizados para diferentes tipos de canchas
    const iconos = {
      futbol5: L.divIcon({
        html: '<div style="background: #10b981; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="fas fa-futbol" style="color: white; font-size: 16px;"></i></div>',
        className: '',
        iconSize: [35, 35]
      }),
      futbol7: L.divIcon({
        html: '<div style="background: #3b82f6; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="fas fa-futbol" style="color: white; font-size: 16px;"></i></div>',
        className: '',
        iconSize: [35, 35]
      }),
      futbol11: L.divIcon({
        html: '<div style="background: #8b5cf6; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="fas fa-futbol" style="color: white; font-size: 16px;"></i></div>',
        className: '',
        iconSize: [35, 35]
      })
    };

    // Grupo de marcadores con clustering
    const markers = L.markerClusterGroup({
      maxClusterRadius: 50,
      spiderfyOnMaxZoom: true,
      showCoverageOnHover: false
    });

    let allMarkers = [];

    // Agregar marcadores de canchas
    function addCanchas(canchas) {
      markers.clearLayers();
      allMarkers = [];
      
      canchas.forEach(cancha => {
        const marker = L.marker([cancha.lat, cancha.lng], {
          icon: iconos[cancha.tipo] || iconos.futbol5
        });

        const popupContent = `
          <div class="popup-header">
            ${cancha.nombre}
          </div>
          <div class="popup-body">
            <p><i class="fas fa-map-marker-alt icon"></i>${cancha.direccion}</p>
            <p><i class="fas fa-football-ball icon"></i>Tipo: ${cancha.tipo.replace('futbol', 'Fútbol ')}</p>
            <p><i class="fas fa-layer-group icon"></i>Superficie: ${cancha.superficie}</p>
            <p><i class="fas fa-map-pin icon"></i>Coords: ${cancha.lat}, ${cancha.lng}</p>
            <a href="https://www.google.com/maps?q=${cancha.lat},${cancha.lng}" target="_blank" class="popup-link">
              <i class="fas fa-directions"></i> Cómo llegar
            </a>
          </div>
        `;

        marker.bindPopup(popupContent, {
          className: 'custom-popup',
          maxWidth: 300
        });

        marker.canchaData = cancha;
        markers.addLayer(marker);
        allMarkers.push(marker);
      });

      map.addLayer(markers);
      updateCanchasList(canchas);
    }

    // Actualizar lista de canchas en el panel
    function updateCanchasList(canchas) {
      const list = document.getElementById('canchasList');
      
      if (canchas.length === 0) {
        list.innerHTML = '<p style="text-align: center; color: #999;">No se encontraron canchas</p>';
        return;
      }

      list.innerHTML = canchas.map(cancha => `
        <div class="cancha-item" onclick="focusCancha(${cancha.id})">
          <h4><i class="fas fa-futbol"></i> ${cancha.nombre}</h4>
          <p><i class="fas fa-map-marker-alt"></i> ${cancha.direccion}</p>
          <span class="tipo-badge">${cancha.tipo.replace('futbol', 'Fútbol ')} - ${cancha.superficie}</span>
        </div>
      `).join('');
    }

    // Enfocar en una cancha específica
    window.focusCancha = function(id) {
      const marker = allMarkers.find(m => m.canchaData.id === id);
      if (marker) {
        map.setView(marker.getLatLng(), 16);
        marker.openPopup();
      }
    };

    // Filtros
    document.querySelectorAll('[data-filter]').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const surfaceFilter = document.querySelector('[data-filter-surface].active').dataset.filterSurface;
        
        filterCanchas(filter, surfaceFilter);
      });
    });

    document.querySelectorAll('[data-filter-surface]').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('[data-filter-surface]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const typeFilter = document.querySelector('[data-filter].active').dataset.filter;
        const surfaceFilter = this.dataset.filterSurface;
        
        filterCanchas(typeFilter, surfaceFilter);
      });
    });

    function filterCanchas(tipo, superficie) {
      let filtered = canchasData;
      
      if (tipo !== 'all') {
        filtered = filtered.filter(c => c.tipo === tipo);
      }
      
      if (superficie !== 'all') {
        filtered = filtered.filter(c => c.superficie === superficie);
      }
      
      addCanchas(filtered);
    }

    // Buscador geocodificador
    const geocoder = L.Control.geocoder({
      defaultMarkGeocode: false,
      placeholder: 'Buscar dirección...',
      collapsed: false
    })
    .on('markgeocode', function(e) {
      const lat = e.geocode.center.lat.toFixed(6);
      const lng = e.geocode.center.lng.toFixed(6);
      
      map.setView(e.geocode.center, 16);
      updateCoords(lat, lng);
      
      L.marker(e.geocode.center).addTo(map)
        .bindPopup(`
          <div class="popup-header">${e.geocode.name}</div>
          <div class="popup-body">
            <p><i class="fas fa-map-pin icon"></i>Coords: ${lat}, ${lng}</p>
            <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="popup-link">
              <i class="fas fa-directions"></i> Ver en Google Maps
            </a>
          </div>
        `, { className: 'custom-popup' })
        .openPopup();
    })
    .addTo(map);

    // Coordenadas y copiar
    const coordsText = document.getElementById('coords');
    const copyBtn = document.getElementById('copyBtn');

    function updateCoords(lat, lng) {
      coordsText.textContent = `${lat}, ${lng}`;
    }

    copyBtn.addEventListener('click', () => {
      navigator.clipboard.writeText(coordsText.textContent);
      copyBtn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
      setTimeout(() => {
        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copiar Coordenadas';
      }, 1500);
    });

    // Click en mapa
    map.on('click', (e) => {
      const lat = e.latlng.lat.toFixed(6);
      const lng = e.latlng.lng.toFixed(6);
      updateCoords(lat, lng);
    });

    // Botón de ubicación
    document.getElementById('locateBtn').addEventListener('click', () => {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          map.setView([lat, lng], 15);
          
          L.marker([lat, lng], {
            icon: L.divIcon({
              html: '<div style="background: #ef4444; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
              className: '',
              iconSize: [20, 20]
            })
          }).addTo(map).bindPopup('Tu ubicación').openPopup();
        });
      } else {
        alert('Geolocalización no disponible');
      }
    });

    // Cargar canchas iniciales
    addCanchas(canchasData);

    // Si hay datos desde PHP, usarlos
    <?php if (isset($canchas) && !empty($canchas)): ?>
      const canchasFromPHP = <?php echo json_encode($canchas); ?>;
      addCanchas(canchasFromPHP);
    <?php endif; ?>
  </script>
</body>
</html>