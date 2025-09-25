<!DOCTYPE html>
<html lang="pt">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Login</title>
 <link rel="stylesheet" href="css/style.css">
 <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

</head>

<body>
 <div class="wrapper">
    <form id="loginForm">
    <h1>Login</h1>
    
    <div class="input-box"> 
        <input type="text" name="username" placeholder="Username" required>
        <i class='bx bxs-user'></i>
    </div>

    <div class="input-box"> 
        <input type="password" name="password" id="password" placeholder="Password" required>
        <i class='bx bx-show toggle-password' id="togglePassword"></i>
    </div>

    <button type="submit" class="btn">Login</button>
    <div id="login-message" style="margin-top:10px; color:red;"></div>
    
    <div class="register-link">
        <p>Não tem conta? <a href="register.php">Regista-te</a></p>
    </div>
    </form>
 </div>

<script>
// Mostrar/ocultar password
document.getElementById("togglePassword").addEventListener("click", function () {
    const passwordInput = document.getElementById("password");
    const isPasswordVisible = passwordInput.type === "text";
    passwordInput.type = isPasswordVisible ? "password" : "text";
    this.classList.toggle("bx-show");
    this.classList.toggle("bx-hide");
});

// Processar o login via AJAX
document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch("process_login.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById("login-message");
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            messageDiv.textContent = data.message;
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        document.getElementById("login-message").textContent = "Erro na comunicação com o servidor.";
    });
});
</script>

</body>
</html>
