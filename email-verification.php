<?php
// Declaraciones "use" movidas al principio del archivo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

// Conexión a la base de datos
$conn = mysqli_connect("localhost", "root", "", "shopping");

if (!$conn) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

if (isset($_POST["verify_email"])) {
    $email = isset($_POST["email"]) ? mysqli_real_escape_string($conn, $_POST["email"]) : '';
    $verification_code = isset($_POST["verification_code"]) ? mysqli_real_escape_string($conn, $_POST["verification_code"]) : '';

    // Marcar el correo electrónico como verificado
    $sql = "UPDATE users SET email_verified_at = NOW() WHERE email = ? AND verification_code = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $verification_code);
    mysqli_stmt_execute($stmt);

    if (mysqli_affected_rows($conn) == 0) {
        die("El código de verificación ha fallado.");
    }

    // Redireccionar a index.php
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="description" content="Página de verificación de correo electrónico">
    <title>Verificación de correo electrónico</title>
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
        <h2>Verificación de correo electrónico</h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="hidden" name="email" value="<?php echo isset($_GET['email']) ? mysqli_real_escape_string($conn, $_GET['email']) : ''; ?>" required>
            <div class="form-group">
                <label for="verification_code">Código de verificación:</label>
                <input type="text" class="form-control" id="verification_code" name="verification_code">
            </div>
            <button type="submit" class="btn btn-primary" name="verify_email">Verificar Email</button>
        </form>
    </div>
</body>
</html>
