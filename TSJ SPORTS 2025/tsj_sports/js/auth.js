// Validación de registro en tiempo real
document.getElementById('formRegistro').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('errorRegistro');
    
    // Resetear mensaje de error
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
    
    // Validar fortaleza de contraseña
    if (password.length < 8) {
        e.preventDefault();
        showError('La contraseña debe tener al menos 8 caracteres');
        return;
    }
    
    if (!/[A-Z]/.test(password)) {
        e.preventDefault();
        showError('La contraseña debe contener al menos una mayúscula');
        return;
    }
    });
    
    function showError(message) {
    const errorDiv = document.getElementById('errorRegistro');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    errorDiv.style.backgroundColor = '#f8d7da';
    errorDiv.style.borderColor = '#f5c6cb';
    }