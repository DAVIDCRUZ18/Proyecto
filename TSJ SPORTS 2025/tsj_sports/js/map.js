// map.js - TSJ SPORTS - Mapa de Canchas de Fútbol

// Datos de ejemplo de canchas (reemplazar con datos de PHP via AJAX o variable global)
let canchasData = [
  { id: 1, nombre: "Cancha El Dorado", lat: 4.7110, lng: -74.0821, tipo: "futbol5", superficie: "sintetica", direccion: "Calle 26 #68D-35" },
  { id: 2, nombre: "Parque Simón Bolívar", lat: 4.6597, lng: -74.0937, tipo: "futbol7", superficie: "natural", direccion: "Av. Calle 63 #48-66" },
  { id: 3, nombre: "Centro Deportivo Salitre", lat: 4.6570, lng: -74.0995, tipo: "futbol11", superficie: "sintetica", direccion: "Av. 68 #56-25" },
  { id: 4, nombre: "Cancha La Florida", lat: 4.6871, lng: -74.0556, tipo: "futbol5", superficie: "sintetica", direccion: "Carrera 7 #145-20" },
  { id: 5, nombre: "Complejo Tunal", lat: 4.5739, lng: -74.1271, tipo: "futbol7", superficie: "natural", direccion: "Av. Boyacá #47B-25 Sur" }
];

// Variables globales
let map;
let markers;
let allMarkers = [];

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

// Función para inicializar el mapa
function initMap() {
  // Inicializar mapa con mejor vista
  map = L.map('map', {
    zoomControl: false
  }).setView([4.6871, -74.0721], 12);

  // Control de zoom personalizado
  L.control.zoom({
    position: 'topleft'
  }).addTo(map);

  // Capa base con CartoDB Voyager (más limpia y moderna)
  L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '© OpenStreetMap contributors © CARTO',
    maxZoom: 19
  }).addTo(map);

  // Grupo de marcadores con clustering
  markers = L.markerClusterGroup({
    maxClusterRadius: 50,
    spiderfyOnMaxZoom: true,
    showCoverageOnHover: false
  });

  // Agregar buscador geocodificador mejorado
  const geocoder = L.Control.geocoder({
    geocoder: L.Control.Geocoder.nominatim({
      geocodingQueryParams: {
        countrycodes: 'co', // Limitar búsqueda solo a Colombia
        viewbox: '-79.0,12.5,-66.0,-4.2', // Bounding box de Colombia
        bounded: 1
      }
    }),
    defaultMarkGeocode: false,
    placeholder: 'Buscar en Colombia...',
    collapsed: false,
    position: 'topleft',
    errorMessage: 'No se encontraron resultados en Colombia',
    showResultIcons: true,
    suggestMinLength: 3,
    suggestTimeout: 250
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

  // Click en mapa para mostrar coordenadas
  map.on('click', (e) => {
    const lat = e.latlng.lat.toFixed(6);
    const lng = e.latlng.lng.toFixed(6);
    updateCoords(lat, lng);
  });

  // Inicializar eventos
  initEvents();
  
  // Cargar canchas iniciales
  addCanchas(canchasData);
}

// Función para agregar marcadores de canchas
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

// Función para actualizar lista de canchas en el panel
function updateCanchasList(canchas) {
  const list = document.getElementById('canchasList');
  
  if (!list) return;
  
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

// Función para enfocar en una cancha específica
function focusCancha(id) {
  const marker = allMarkers.find(m => m.canchaData.id === id);
  if (marker) {
    map.setView(marker.getLatLng(), 16);
    marker.openPopup();
  }
}

// Función para filtrar canchas
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

// Función para actualizar coordenadas en el cuadro
function updateCoords(lat, lng) {
  const coordsText = document.getElementById('coords');
  if (coordsText) {
    coordsText.textContent = `${lat}, ${lng}`;
  }
}

// Función para inicializar eventos
function initEvents() {
  // Filtros de tipo de cancha
  document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      const filter = this.dataset.filter;
      const surfaceBtn = document.querySelector('[data-filter-surface].active');
      const surfaceFilter = surfaceBtn ? surfaceBtn.dataset.filterSurface : 'all';
      
      filterCanchas(filter, surfaceFilter);
    });
  });

  // Filtros de superficie
  document.querySelectorAll('[data-filter-surface]').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('[data-filter-surface]').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      const typeBtn = document.querySelector('[data-filter].active');
      const typeFilter = typeBtn ? typeBtn.dataset.filter : 'all';
      const surfaceFilter = this.dataset.filterSurface;
      
      filterCanchas(typeFilter, surfaceFilter);
    });
  });

  // Botón copiar coordenadas
  const copyBtn = document.getElementById('copyBtn');
  if (copyBtn) {
    copyBtn.addEventListener('click', () => {
      const coordsText = document.getElementById('coords');
      if (coordsText && coordsText.textContent !== 'Haz clic en el mapa') {
        navigator.clipboard.writeText(coordsText.textContent);
        copyBtn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
        setTimeout(() => {
          copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copiar Coordenadas';
        }, 1500);
      }
    });
  }

  // Botón de ubicación
  const locateBtn = document.getElementById('locateBtn');
  if (locateBtn) {
    locateBtn.addEventListener('click', () => {
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
        }, (error) => {
          alert('No se pudo obtener tu ubicación');
          console.error(error);
        });
      } else {
        alert('Geolocalización no disponible en tu navegador');
      }
    });
  }
}

// Función para cargar canchas desde el servidor (AJAX)
function loadCanchasFromServer() {
  // Ejemplo usando fetch - ajusta la URL según tu API
  fetch('/TSJ SPORTS 2025/tsj_sports/php/get_canchas.php')
    .then(response => response.json())
    .then(data => {
      canchasData = data;
      addCanchas(canchasData);
    })
    .catch(error => {
      console.error('Error cargando canchas:', error);
    });
}

// Función para establecer canchas desde variable global (si vienen de PHP)
function setCanchasFromPHP(canchasFromPHP) {
  if (canchasFromPHP && Array.isArray(canchasFromPHP)) {
    canchasData = canchasFromPHP;
    addCanchas(canchasData);
  }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  initMap();
  
  // Si existe una variable global con canchas de PHP, usarla
  if (typeof window.canchasFromPHP !== 'undefined') {
    setCanchasFromPHP(window.canchasFromPHP);
  }
});

// Exponer funciones globales
window.focusCancha = focusCancha;
window.setCanchasFromPHP = setCanchasFromPHP;
window.loadCanchasFromServer = loadCanchasFromServer;


