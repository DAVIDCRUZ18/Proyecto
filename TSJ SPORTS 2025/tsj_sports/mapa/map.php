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
  <title>Mapa - TSJ SPORTS</title>

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
  <link rel="stylesheet" href="/TSJ SPORTS 2025/tsj_sports/css/map.css">
  <link rel="stylesheet" href="/TSJ SPORTS 2025/tsj_sports/css/stylesP.css">

</head>
<body>



  <div id="map"></div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

  <script>
    const map = L.map('map').setView([4.7110, -74.0721], 12); // Bogotá

    // Capa base
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Buscador
    const geocoder = L.Control.geocoder({
      defaultMarkGeocode: false
    })
    .on('markgeocode', function(e) {
      const lat = e.geocode.center.lat.toFixed(6);
      const lng = e.geocode.center.lng.toFixed(6);
      const marker = L.marker(e.geocode.center).addTo(map);
      marker.bindPopup(`
        <b>${e.geocode.name}</b><br>
        Coordenadas: ${lat}, ${lng}<br>
        <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank">Ver en Google Maps</a>
      `).openPopup();
      map.setView(e.geocode.center, 16);
      updateCoords(lat, lng);
    })
    .addTo(map);

    // Cuadro de coordenadas
    const coordsBox = L.DomUtil.create('div', 'coords-box');
    coordsBox.innerHTML = `
      <strong>Coordenadas:</strong> <span id="coords">—</span><br>
      <button id="copyBtn">Copiar</button>
    `;
    document.body.appendChild(coordsBox);

    const coordsText = document.getElementById('coords');
    const copyBtn = document.getElementById('copyBtn');

    function updateCoords(lat, lng) {
      coordsText.textContent = `${lat}, ${lng}`;
    }

    copyBtn.addEventListener('click', () => {
      navigator.clipboard.writeText(coordsText.textContent);
      copyBtn.textContent = '¡Copiado!';
      setTimeout(() => copyBtn.textContent = 'Copiar', 1500);
    });

    // Click en mapa para marcar punto
    map.on('click', (e) => {
      const lat = e.latlng.lat.toFixed(6);
      const lng = e.latlng.lng.toFixed(6);
      L.marker(e.latlng).addTo(map)
        .bindPopup(`
          <b>Punto seleccionado</b><br>
          Coordenadas: ${lat}, ${lng}<br>
          <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank">Ver en Google Maps</a>
        `)
        .openPopup();
      updateCoords(lat, lng);
    });

    // Si tienes canchas desde PHP, las mostramos
    <?php if (isset($canchas) && !empty($canchas)): ?>
      const canchas = <?php echo json_encode($canchas); ?>;
      canchas.forEach(cancha => {
        L.marker([cancha.lat, cancha.lng]).addTo(map)
          .bindPopup(`<b>${cancha.nombre}</b><br>
            Coordenadas: ${cancha.lat}, ${cancha.lng}<br>
            <a href="https://www.google.com/maps?q=${cancha.lat},${cancha.lng}" target="_blank">Ver en Google Maps</a>
          `);
      });
    <?php endif; ?>
  </script>

</body>
</html>