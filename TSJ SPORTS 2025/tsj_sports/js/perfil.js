// Cargar perfil al iniciar
document.addEventListener('DOMContentLoaded', () => {
    cargarPerfil();
    
    // Evento para cambiar foto
    document.getElementById('upload').addEventListener('change', subirFoto);
});

// Cargar datos del perfil
async function cargarPerfil() {
    try {
        const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/perfil/obtener_perfil.php');
        const data = await response.json();
        
        if (data.success) {
            const perfil = data.perfil;
            
            // Actualizar foto de perfil
            document.getElementById('profileImage').src = perfil.foto_perfil;
            
            // Actualizar campos de redes sociales
            document.getElementById('instagram').value = perfil.instagram || '';
            document.getElementById('twitter').value = perfil.twitter || '';
            document.getElementById('facebook').value = perfil.facebook || '';
            
            // Actualizar estadísticas
            document.getElementById('teamDisplay').textContent = perfil.equipo;
            document.getElementById('goalsDisplay').textContent = perfil.goles;
            document.getElementById('matchesDisplay').textContent = perfil.partidos;
            
            // Actualizar enlaces de redes sociales
            actualizarEnlaces(perfil);
        } else {
            console.error('Error al cargar perfil:', data.message);
        }
    } catch (error) {
        console.error('Error de conexión:', error);
    }
}

// Subir foto de perfil
async function subirFoto(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Validar tipo de archivo
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('Por favor selecciona una imagen válida (JPG, PNG, GIF o WebP)');
        return;
    }
    
    // Validar tamaño (máximo 5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('La imagen es muy grande. Máximo 5MB');
        return;
    }
    
    // Mostrar preview inmediato
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('profileImage').src = e.target.result;
    };
    reader.readAsDataURL(file);
    
    // Subir al servidor
    const formData = new FormData();
    formData.append('foto', file);
    
    try {
        const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/perfil/subir_foto.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Foto actualizada correctamente');
            // Actualizar la imagen con la URL del servidor
            document.getElementById('profileImage').src = data.foto_url;
        } else {
            alert('Error: ' + data.message);
            // Recargar la foto original si hubo error
            cargarPerfil();
        }
    } catch (error) {
        alert('Error de conexión al subir la foto');
        console.error(error);
        cargarPerfil();
    }
}

// Guardar perfil (redes sociales)
async function saveProfile() {
    const instagram = document.getElementById('instagram').value.trim();
    const twitter = document.getElementById('twitter').value.trim();
    const facebook = document.getElementById('facebook').value.trim();
    
    try {
        const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/perfil/guardar_perfil.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                instagram,
                twitter,
                facebook
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Perfil guardado correctamente');
            // Actualizar enlaces
            actualizarEnlaces({
                instagram: data.data.instagram,
                twitter: data.data.twitter,
                facebook: data.data.facebook
            });
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error de conexión');
        console.error(error);
    }
}

// Actualizar enlaces de redes sociales
function actualizarEnlaces(perfil) {
    const igLink = document.getElementById('linkInstagram');
    const twLink = document.getElementById('linkTwitter');
    const fbLink = document.getElementById('linkFacebook');
    
    if (perfil.instagram) {
        igLink.href = `https://instagram.com/${perfil.instagram}`;
        igLink.style.display = 'inline-block';
    } else {
        igLink.style.display = 'none';
    }
    
    if (perfil.twitter) {
        twLink.href = `https://twitter.com/${perfil.twitter}`;
        twLink.style.display = 'inline-block';
    } else {
        twLink.style.display = 'none';
    }
    
    if (perfil.facebook) {
        fbLink.href = `https://facebook.com/${perfil.facebook}`;
        fbLink.style.display = 'inline-block';
    } else {
        fbLink.style.display = 'none';
    }
}