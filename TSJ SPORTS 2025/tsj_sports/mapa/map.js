// Inicializar el mapa en Bogotá
const map = L.map('map').setView([4.7110, -74.0721], 12);

// Cargar el mapa base desde OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '© OpenStreetMap'
}).addTo(map);

// Agregar un marcador en Bogotá
L.marker([4.7110, -74.0721])
  .addTo(map)
  .bindPopup("<b>Bogotá</b><br>Colombia")
  .openPopup();
