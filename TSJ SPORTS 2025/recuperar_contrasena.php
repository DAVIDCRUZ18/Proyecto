<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - TSJ SPORTS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .recovery-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .recovery-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
        }

        .recovery-box h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 10px;
            font-size: 32px;
        }

        .recovery-box p {
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-box {
            position: relative;
            margin-bottom: 25px;
        }

        .input-box input {
            width: 100%;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 40px;
            padding: 0 45px 0 20px;
            color: #fff;
            font-size: 16px;
            outline: none;
            transition: 0.3s;
        }

        .input-box input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .input-box input:focus {
            border-color: #00d4ff;
        }

        .input-box .icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #fff;
            font-size: 20px;
        }

        .code-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 25px;
        }

        .code-inputs input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            outline: none;
            transition: 0.3s;
        }

        .code-inputs input:focus {
            border-color: #4b9aaaff;
            transform: scale(1.05);
        }

        .btn {
            width: 100%;
            height: 50px;
            background: linear-gradient(45deg, #5197a5ff, #5185a8ff);
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 25px rgba(0, 212, 255, 0.5);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #4b8996ff;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .back-link a:hover {
            color: #fff;
        }

        .message {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            display: none;
        }

        .message.success {
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid rgba(0, 255, 0, 0.3);
            color: #0f0;
        }

        .message.error {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ff6b6b;
        }

        .message.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dev-note {
            background: rgba(255, 165, 0, 0.1);
            border: 1px solid rgba(255, 165, 0, 0.3);
            color: #ffa500;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-box">
            <h2>Recuperar Contraseña</h2>
            
            <div id="message" class="message"></div>

            <!-- Paso 1: Ingresar email -->
            <div class="step active" id="step1">
                <p>Ingresa tu email y te enviaremos un código de verificación</p>
                <form id="emailForm">
                    <div class="input-box">
                        <span class="icon">
                            <ion-icon name="mail"></ion-icon>
                        </span>
                        <input type="email" id="email" placeholder="Email" required>
                    </div>
                    <button type="submit" class="btn" id="sendCodeBtn">Enviar código</button>
                </form>
            </div>

            <!-- Paso 2: Verificar código -->
            <div class="step" id="step2">
                <p>Ingresa el código de 6 dígitos enviado a tu email</p>
                <form id="codeForm">
                    <div class="code-inputs">
                        <input type="text" maxlength="1" class="code-input" data-index="0">
                        <input type="text" maxlength="1" class="code-input" data-index="1">
                        <input type="text" maxlength="1" class="code-input" data-index="2">
                        <input type="text" maxlength="1" class="code-input" data-index="3">
                        <input type="text" maxlength="1" class="code-input" data-index="4">
                        <input type="text" maxlength="1" class="code-input" data-index="5">
                    </div>
                    <button type="submit" class="btn" id="verifyCodeBtn">Verificar código</button>
                </form>
                <div id="devCode" class="dev-note" style="display: none;"></div>
            </div>

            <!-- Paso 3: Nueva contraseña -->
            <div class="step" id="step3">
                <p>Ingresa tu nueva contraseña</p>
                <form id="passwordForm">
                    <div class="input-box">
                        <span class="icon">
                            <ion-icon name="lock-closed"></ion-icon>
                        </span>
                        <input type="password" id="newPassword" placeholder="Nueva contraseña" required minlength="6">
                    </div>
                    <div class="input-box">
                        <span class="icon">
                            <ion-icon name="lock-closed"></ion-icon>
                        </span>
                        <input type="password" id="confirmPassword" placeholder="Confirmar contraseña" required minlength="6">
                    </div>
                    <button type="submit" class="btn" id="changePasswordBtn">Cambiar contraseña</button>
                </form>
            </div>

            <div class="back-link">
                <a href="index.html">← Volver al inicio de sesión</a>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    
    <script>
        let currentEmail = '';
        let currentToken = '';

        // Manejo del formulario de email
        document.getElementById('emailForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const btn = document.getElementById('sendCodeBtn');
            
            btn.disabled = true;
            btn.textContent = 'Enviando...';

            try {
                const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/recuperar/enviar_codigo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });

                const data = await response.json();

                if (data.success) {
                    currentEmail = email;
                    showMessage(data.message, 'success');
                    
                    // Mostrar código en desarrollo
                    if (data.dev_code) {
                        const devNote = document.getElementById('devCode');
                        devNote.textContent = `MODO DESARROLLO - Código: ${data.dev_code}`;
                        devNote.style.display = 'block';
                    }
                    
                    setTimeout(() => {
                        goToStep(2);
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('Error de conexión. Intenta nuevamente.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Enviar código';
            }
        });

        // Manejo del código de verificación
        const codeInputs = document.querySelectorAll('.code-input');
        
        codeInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < codeInputs.length - 1) {
                    codeInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    codeInputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').slice(0, 6);
                pastedData.split('').forEach((char, i) => {
                    if (codeInputs[i]) codeInputs[i].value = char;
                });
                if (pastedData.length === 6) codeInputs[5].focus();
            });
        });

        document.getElementById('codeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const code = Array.from(codeInputs).map(input => input.value).join('');
            
            if (code.length !== 6) {
                showMessage('Ingresa el código completo', 'error');
                return;
            }

            const btn = document.getElementById('verifyCodeBtn');
            btn.disabled = true;
            btn.textContent = 'Verificando...';

            try {
                const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/recuperar/verificar_codigo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: currentEmail, code })
                });

                const data = await response.json();

                if (data.success) {
                    currentToken = data.token;
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        goToStep(3);
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                    codeInputs.forEach(input => input.value = '');
                    codeInputs[0].focus();
                }
            } catch (error) {
                showMessage('Error de conexión. Intenta nuevamente.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Verificar código';
            }
        });

        // Manejo del formulario de nueva contraseña
        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                showMessage('Las contraseñas no coinciden', 'error');
                return;
            }

            if (newPassword.length < 6) {
                showMessage('La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }

            const btn = document.getElementById('changePasswordBtn');
            btn.disabled = true;
            btn.textContent = 'Cambiando...';

            try {
                const response = await fetch('/TSJ SPORTS 2025/tsj_sports/php/recuperar/cambiar_contrasena.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        email: currentEmail, 
                        token: currentToken, 
                        password: newPassword 
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('¡Contraseña actualizada! Redirigiendo...', 'success');
                    setTimeout(() => {
                        window.location.href = 'inicio.html';
                    }, 2000);
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('Error de conexión. Intenta nuevamente.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Cambiar contraseña';
            }
        });

        function goToStep(stepNumber) {
            document.querySelectorAll('.step').forEach(step => {
                step.classList.remove('active');
            });
            document.getElementById(`step${stepNumber}`).classList.add('active');
        }

        function showMessage(text, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = text;
            messageDiv.className = `message ${type} show`;
            
            setTimeout(() => {
                messageDiv.classList.remove('show');
            }, 5000);
        }

        // Auto-focus en el primer input
        document.getElementById('email').focus();
    </script>
</body>
</html>