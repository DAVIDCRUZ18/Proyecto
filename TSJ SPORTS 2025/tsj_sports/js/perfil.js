// Cargar imagen de perfil
document.getElementById('upload').addEventListener('change', function(event) {
  const reader = new FileReader();
  reader.onload = function() {
    document.getElementById('profileImage').src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
});

// Cargar datos desde PHP
window.addEventListener('DOMContentLoaded', () => {
  fetch('jugador.php')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error(data.error);
        return;
      }

      document.getElementById('teamDisplay').textContent = data.equipo;
      document.getElementById('goalsDisplay').textContent = data.goles;
      document.getElementById('matchesDisplay').textContent = data.partidos;
    })
    .catch(err => console.error("Error al cargar datos del jugador:", err));
});

function saveProfile() {
  const ig = document.getElementById('instagram').value.trim();
  const tw = document.getElementById('twitter').value.trim();
  const fb = document.getElementById('facebook').value.trim();

  document.getElementById('linkInstagram').href = ig ? `https://instagram.com/${ig.replace('@','')}` : "#";
  document.getElementById('linkTwitter').href = tw ? `https://twitter.com/${tw.replace('@','')}` : "#";
  document.getElementById('linkFacebook').href = fb ? `https://facebook.com/${fb}` : "#";
}

