<?php
// login.php
session_start();
require_once 'config.php'; // Incluye el archivo de configuración de la base de datos

$username = $password = "";
$username_err = $password_err = $login_err = "";

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar usuario
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor, ingresa tu usuario.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validar contraseña
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, ingresa tu contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Si no hay errores de entrada, intentar iniciar sesión
    if (empty($username_err) && empty($password_err)) {
        // Preparar una sentencia SELECT
        $sql = "SELECT id, username, password_hash FROM usuarios WHERE username = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Vincular variables a la sentencia preparada como parámetros
            $stmt->bind_param("s", $param_username);

            // Establecer parámetros
            $param_username = $username;

            // Ejecutar la sentencia preparada
            if ($stmt->execute()) {
                // Almacenar el resultado
                $stmt->store_result();

                // Verificar si el usuario existe, si sí, verificar la contraseña
                if ($stmt->num_rows == 1) {
                    // Vincular variables de resultado
                    $stmt->bind_result($id, $username, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // La contraseña es correcta, iniciar una nueva sesión
                            session_start();

                            // Almacenar datos en variables de sesión
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            // Redirigir al usuario a la página de bienvenida (o createorder.php)
                            header("location: createorder.php");
                        } else {
                            // La contraseña no es válida, mostrar un mensaje de error genérico
                            $login_err = "Usuario o contraseña inválidos.";
                        }
                    }
                } else {
                    // El usuario no existe, mostrar un mensaje de error genérico
                    $login_err = "Usuario o contraseña inválidos.";
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            }

            // Cerrar sentencia
            $stmt->close();
        }
    }

    // Cerrar conexión
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        .login-container h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .help-block {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
            display: block;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Iniciar Sesión">
            </div>
        </form>
    </div>
</body>
</html>