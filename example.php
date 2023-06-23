<?php
// Declaraciones "use" movidas al principio del archivo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

// Función para verificar si la contraseña es segura
function isPasswordSecure($password) {
    // Verifica si la contraseña tiene al menos una mayúscula, un número y un carácter especial
    return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

// Manejar el envío del formulario y registro de usuarios
if (isset($_POST["register"])) {
    $name = $_POST["fullname"];
    $email = $_POST["emailid"];
    $contactno = $_POST["contactno"];
    $password = $_POST["password"];

    // Verificar si la contraseña es segura
    if (!isPasswordSecure($password)) {
        echo "La contraseña debe contener al menos una mayúscula, un número y un carácter especial.";
        exit();
    }

    // Crear instancia de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuración de SMTP
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'vmanuelam82@gmail.com';
        $mail->Password = 'epcxevicqlmbbcit';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('vmanuelam82@gmail.com', 'remitente');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
        $mail->Subject = 'Verificación de correo electrónico';
        $mail->Body = '<p>Su código de verificación es: <b style="font-size: 30px;">' . $verification_code . '</b></p>';

        // Enviar correo
        $mail->send();

        // Conexión a la base de datos
               // Conexión a la base de datos
               $conn = mysqli_connect("localhost", "root", "", "shopping");

               if (!$conn) {
                   die("Error de conexión a la base de datos: " . mysqli_connect_error());
               }
       
               // Insertar datos en la tabla de usuarios
               $sql = "INSERT INTO users (name, email, contactno, password, verification_code) VALUES ('$name', '$email', '$contactno', '$password', '$verification_code')";
       
               if (mysqli_query($conn, $sql)) {
                   echo "Registro exitoso. Se ha enviado un correo de verificación a su dirección de correo electrónico.";
               } else {
                   echo "Error: " . $sql . "<br>" . mysqli_error($conn);
               }
       
               // Cerrar conexión a la base de datos
               mysqli_close($conn);
           } catch (Exception $e) {
               echo "Error al enviar el correo: " . $mail->ErrorInfo;
           }
       }
       ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="description" content="Página de registro">
    <title>Registro</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        .container {
            max-width: 500px;
            margin: 0 auto;
            margin-top: 100px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Registro de usuario</h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
                <label for="fullname">Nombre completo:</label>
                <input type="text" class="form-control" id="fullname" name="fullname" required>
            </div>
            <div class="form-group">
                <label for="emailid">Correo electrónico:</label>
                <input type="email" class="form-control" id="emailid" name="emailid" required>
            </div>
            <div class="form-group">
                <label for="contactno">Número de contacto:</label>
                <input type="text" class="form-control" id="contactno" name="contactno" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" name="register">Registrarse</button>
        </form>
    </div>
</body>
</html>
       