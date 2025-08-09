<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="./favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="./assets/css/all.min.css">
    <link rel="stylesheet" href="./css/login.css">
</head>
<body id="login">
    <div class="login-box">
    <h2>Iniciar sesión</h2>
    <form id="formLogin">
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" required>
        </div>
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="clave" name="clave" placeholder="*********" required>
        </div>
      <button id="btnIngresar" type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>

    <div id="alertaLogin" style="color: red; display: none;"></div>

    <div class="login-links">
      <a href="#">¿Olvidó su contraseña?</a> <br>
    </div>
  </div>

    <script type="module" src="./js/login.js"></script>
</body>
</html>