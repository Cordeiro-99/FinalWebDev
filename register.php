<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registo</title>
  <link rel="stylesheet" href="css/style.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <script>
    $(document).ready(function () {
      $('#register-form').submit(function (event) {
        event.preventDefault();

        // Captura os valores dos campos

        let username = $('#username').val().trim();
        let email = $('#email').val().trim();
        let password = $('#password').val();
        let confirmPassword = $('#confirm-password').val();
        let role = $('#role').val();
        let errorMessage = '';
        
        // Validação do nome de utilizador
        if (username.length < 3) {
          errorMessage = 'O nome de utilizador deve ter pelo menos 3 caracteres.';
        }

        // Validação do e-mail
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
          errorMessage = 'Por favor, insira um e-mail válido.';
        }
        // Validação da password
        if (password.length < 6) {
          errorMessage = 'A senha deve ter pelo menos 6 caracteres.';
        }
        // Verifica se a password e a confirmação são iguais
        if (password !== confirmPassword) {
          errorMessage = 'As senhas não correspondem.';
        }
        // Exibe mensagem de erro se houver
        if (errorMessage !== '') {
          $('#error-message').text(errorMessage).css('color', 'red');
          return;
        }
        // Envia o formulário via AJAX
        let formData = new FormData(this);
        $.ajax({
          url: 'process_register.php',
          type: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function (response) {
            try {
              response = JSON.parse(response);
              if (response.success) {
                $('#error-message').text(response.message).css('color', 'lightgreen');
                setTimeout(() => {
                  window.location.href = 'login.php';
                }, 2000);
              } else {
                $('#error-message').text(response.message).css('color', 'red');
              }
            } catch (e) {
              $('#error-message').text('Erro no formato da resposta.').css('color', 'red');
            }
          },
          error: function () {
            $('#error-message').text('Erro ao processar o registro.').css('color', 'red');
          }
        });
      });
    });
  </script>
</head>

<body>
  <div class="wrapper">
    <form id="register-form" enctype="multipart/form-data">
      <h1>Registo</h1>

      <div class="input-box">
        <input type="text" id="username" name="username" placeholder="Nome de utilizador" required>
        <i class='bx bxs-user'></i>
      </div>

      <div class="input-box">
        <input type="email" id="email" name="email" placeholder="Email" required>
        <i class='bx bxs-envelope'></i>
      </div>

      <div class="input-box">
        <input type="password" id="password" name="password" placeholder="Senha" required>
        <i class='bx bxs-lock-alt'></i>
      </div>

      <div class="input-box">
        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirmar Senha" required>
        <i class='bx bxs-lock'></i>
      </div>

   <div class="input-box">
  <select id="role" name="role" required class="custom-select">
    <option value="user">Utilizador</option>
  </select>
</div>

      <button type="submit" class="btn">Registar</button>

      <div class="register-link">
        <p>Já tem conta? <a href="login.php">Entrar</a></p>
      </div>
      <p id="error-message" class="message" style="text-align: center;"></p>
    </form>
  </div>
</body>
</html>
