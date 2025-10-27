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

  <!-- Librerías -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
  <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
  <script src="https://kit.fontawesome.com/a2e0d5a23b.js" crossorigin="anonymous"></script>

  <!-- Tu script -->
  <script src="/TSJ SPORTS 2025/tsj_sports/js/map.js"></script>
</body>
</html>