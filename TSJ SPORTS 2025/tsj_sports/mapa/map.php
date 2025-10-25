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
  <link rel="stylesheet" href="/TSJ SPORTS 2025/tsj_sports/css/map.css" />
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