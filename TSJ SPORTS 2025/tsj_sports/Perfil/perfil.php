<?php
session_start();
// Verificar que haya sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /TSJ SPORTS 2025/tsj_sports/inicio.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Perfil del Jugador - TSJ SPORTS</title>
  <link rel="stylesheet" href="../css/StylesP.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    .profile-container {
      max-width: 600px;
      margin: 50px auto;
      padding: 30px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    
    .profile-pic {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      display: block;
      margin: 0 auto 20px;
      border: 4px solid #00d4ff;
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    
    .profile-pic:hover {
      transform: scale(1.05);
    }
    
    .upload-btn {
      text-align: center;
      color: #00d4ff;
      cursor: pointer;
      margin-bottom: 30px;
      font-weight: 600;
      transition: color 0.3s;
    }
    
    .upload-btn:hover {
      color: #fff;
    }
    
    .socials {
      margin-bottom: 20px;
    }
    
    .socials label {
      display: block;
      color: #fff;
      margin-bottom: 8px;
      font-weight: 600;
    }
    
    .socials input {
      width: 100%;
      padding: 12px 20px;
      background: rgba(255, 255, 255, 0.1);
      border: 2px solid rgba(255, 255, 255, 0.2);
      border-radius: 10px;
      color: #fff;
      font-size: 16px;
      outline: none;
      transition: 0.3s;
    }
    
    .socials input:focus {
      border-color: #00d4ff;
    }
    
    .socials input::placeholder {
      color: rgba(255, 255, 255, 0.5);
    }
    
    .save-btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(45deg, #00d4ff, #0099ff);
      border: none;
      border-radius: 10px;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      margin: 20px 0;
      transition: 0.3s;
    }
    
    .save-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(0, 212, 255, 0.5);
    }
    
    .stat {
      background: rgba(0, 0, 0, 0.3);
      padding: 20px;
      border-radius: 10px;
      margin: 20px 0;
    }
    
    .stat p {
      color: #fff;
      margin: 10px 0;
      font-size: 16px;
    }
    
    .stat strong {
      color: #00d4ff;
    }
    
    .socials a {
      display: inline-block;
      margin: 10px 10px 0 0;
      padding: 10px 20px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
    }
    
    .socials a:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.2);
    }
  </style>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/header.php"; ?>
  
  <div class="profile-container">
    <h1 style="color: #fff; text-align: center; margin-bottom: 30px;">Mi Perfil</h1>
    
    <!-- Foto de perfil -->
    <input type="file" id="upload" style="display:none" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" />
    <img src="https://via.placeholder.com/120" alt="Foto de perfil" id="profileImage" class="profile-pic" onclick="document.getElementById('upload').click()">
    <div class="upload-btn" onclick="document.getElementById('upload').click()">📷 Cambiar Foto</div>
    
    <!-- Estadísticas -->
    <div class="stat">
      <p><strong>Equipo:</strong> <span id="teamDisplay">Cargando...</span></p>
      <p><strong>Goles:</strong> <span id="goalsDisplay">0</span></p>
      <p><strong>Partidos:</strong> <span id="matchesDisplay">0</span></p>
    </div>
    
    <!-- Redes sociales -->
    <h3 style="color: #fff; margin: 30px 0 20px;">Redes Sociales</h3>
    
    <div class="socials">
      <label>📷 Instagram</label>
      <input type="text" id="instagram" placeholder="@usuario" />
    </div>
    
    <div class="socials">
      <label>🐦 Twitter</label>
      <input type="text" id="twitter" placeholder="@usuario" />
    </div>
    
    <div class="socials">
      <label>📘 Facebook</label>
      <input type="text" id="facebook" placeholder="nombreusuario" />
    </div>
    
    <button class="save-btn" onclick="saveProfile()">💾 Guardar Perfil</button>
    
    <!-- Enlaces a redes sociales -->
    <div class="socials" style="text-align: center;">
      <a id="linkInstagram" target="_blank" style="color:#ff0055; display: none;">Instagram</a>
      <a id="linkTwitter" target="_blank" style="color:#009dff; display: none;">Twitter</a>
      <a id="linkFacebook" target="_blank" style="color:#1900ff; display: none;">Facebook</a>
    </div>
  </div>
  
  <script src="../js/perfil.js"></script>
</body>
</html>