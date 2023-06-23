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

// Manejar el envío del formulario
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
        $conn = mysqli_connect("localhost", "root", "", "shopping");

        if (!$conn) {
            die("Error de conexión a la base de datos: " . mysqli_connect_error());
        }

        // Almacenar el código de verificación en la base de datos
        $sql = "UPDATE users SET verification_code = ? WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $verification_code, $email);
        mysqli_stmt_execute($stmt);

        // Encriptar la contraseña
        $encrypted_password = md5($password);

        // Insertar en la tabla de usuarios
        $sql = "INSERT INTO users(name, email, contactno, password) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $contactno, $encrypted_password);
        mysqli_stmt_execute($stmt);

        // Mostrar mensaje de registro exitoso
        echo "<script>alert('Registro exitoso');</script>";
        echo "<script>window.location.href = 'email-verification.php?email=$email';</script>";
    } catch (Exception $e) {
        echo "No se pudo enviar el mensaje. Error del remitente: {$mail->ErrorInfo}";
    }
}

session_start();
error_reporting(0);
include('includes/config.php');

if (isset($_POST['submit'])) {
    $name = $_POST['fullname'];
    $email = $_POST['emailid'];
    $contactno = $_POST['contactno'];
    $password = md5($_POST['password']);
    $query = mysqli_query($con, "insert into users(name,email,contactno,password) values('$name','$email','$contactno','$password')");
    if ($query) {
        echo "<script>alert('Registro exitoso');</script>";
    } else {
        echo "<script>alert('No se pudo registrar, algo salió mal');</script>";
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' and password='$password'");
    $num = mysqli_fetch_array($query);
    if ($num > 0) {
        $extra = "my-cart.php";
        $_SESSION['login'] = $_POST['email'];
        $_SESSION['id'] = $num['id'];
        $_SESSION['username'] = $num['name'];
        $uip = $_SERVER['REMOTE_ADDR'];
        $status = 1;
        $log = mysqli_query($con, "insert into userlog(userEmail,userip,status) values('".$_SESSION['login']."','$uip','$status')");
        $host = $_SERVER['HTTP_HOST'];
        $uri = rtrim(dirname($_SERVER['PHP_SELF']),'/\\');
        header("location:http://$host$uri/$extra");
        exit();
    } else {
        $extra = "login.php";
        $email = $_POST['email'];
        $uip = $_SERVER['REMOTE_ADDR'];
        $status = 0;
        $log = mysqli_query($con, "insert into userlog(userEmail,userip,status) values('$email','$uip','$status')");
        $host  = $_SERVER['HTTP_HOST'];
        $uri  = rtrim(dirname($_SERVER['PHP_SELF']),'/\\');
        header("location:http://$host$uri/$extra");
        $_SESSION['errmsg'] = "ID de correo electrónico o contraseña no válidos";
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<meta name="keywords" content="MediaCenter, Template, eCommerce">
	<meta name="robots" content="all">
	<title>Portal de compras | Iniciar sesión | Registrarse</title>
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/main.css">
	<link rel="stylesheet" href="assets/css/green.css">
	<link rel="stylesheet" href="assets/css/owl.carousel.css">
	<link rel="stylesheet" href="assets/css/owl.transitions.css">
	<link href="assets/css/lightbox.css" rel="stylesheet">
	<link rel="stylesheet" href="assets/css/animate.min.css">
	<link rel="stylesheet" href="assets/css/rateit.css">
	<link rel="stylesheet" href="assets/css/bootstrap-select.min.css">
	<link rel="stylesheet" href="assets/css/config.css">
	<link href="assets/css/green.css" rel="alternate stylesheet" title="Green color">
	<link href="assets/css/blue.css" rel="alternate stylesheet" title="Blue color">
	<link href="assets/css/red.css" rel="alternate stylesheet" title="Red color">
	<link href="assets/css/orange.css" rel="alternate stylesheet" title="Orange color">
	<link href="assets/css/dark-green.css" rel="alternate stylesheet" title="Darkgreen color">
	
	<link rel="stylesheet" href="assets/css/font-awesome.min.css">
	
	<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet' type='text/css'>
	
	<link rel="shortcut icon" href="assets/images/favicon.ico">
	<script type="text/javascript">
	function valid()
	{
		if(document.register.password.value!= document.register.confirmpassword.value)
		{
			alert("La contraseña y la confirmación de contraseña no coinciden!!");
			document.register.confirmpassword.focus();
			return false;
		}
		return true;
	}
	</script>
	<script>
	function userAvailability() {
		$("#loaderIcon").show();
		jQuery.ajax({
			url: "check_availability.php",
			data:'email='+$("#email").val(),
			type: "POST",
			success:function(data){
				$("#user-availability-status1").html(data);
				$("#loaderIcon").hide();
			},
			error:function (){}
		});
	}
	</script>
</head>
<body class="cnt-home">
<header class="header-style-1">
	<?php include('includes/top-header.php');?>
	<?php include('includes/main-header.php');?>
    <?php include('includes/menu-bar.php');?>
	</header>
<div class="breadcrumb">
	<div class="container">
		<div class="breadcrumb-inner">
			<ul class="list-inline list-unstyled">
				<li><a href="index.php">Inicio</a></li>
				<li class='active'>Rgistrarte</li>
			</ul>
		</div>
	</div>
</div>
<div class="body-content outer-top-bd">
	<div class="container">
		<div class="sign-in-page inner-bottom-sm">
			<div class="row">
				<div class="col-md-6 col-sm-6 create-new-account">
					<h4 class="checkout-subtitle">Crear una nueva cuenta</h4>
					<p class="text title-tag-line">Crea tu propia cuenta de compras.</p>
					<form class="register-form outer-top-xs" role="form" method="post" name="register" onSubmit="return valid();">
						<div class="form-group">
							<label class="info-title" for="fullname">Nombre completo <span>*</span></label>
							<input type="text" class="form-control unicase-form-control text-input" id="fullname" name="fullname" required="required">
						</div>
						<div class="form-group">
							<label class="info-title" for="exampleInputEmail2">Correo electrónico <span>*</span></label>
							<input type="email" class="form-control unicase-form-control text-input" id="email" onBlur="userAvailability()" name="emailid" required >
							<span id="user-availability-status1" style="font-size:12px;"></span>
						</div>
							<div class="form-group">
							<label class="info-title" for="contactno">Número de contacto <span>*</span></label>
							<input type="text" class="form-control unicase-form-control text-input" id="contactno" name="contactno" maxlength="10" required >
						</div>
						<div class="form-group">
							<label class="info-title" for="password">Contraseña <span>*</span></label>
							<input type="password" class="form-control unicase-form-control text-input" id="password" name="password"  required >
						</div>
						<div class="form-group">
							<label class="info-title" for="confirmpassword">Confirmar contraseña <span>*</span></label>
							<input type="password" class="form-control unicase-form-control text-input" id="confirmpassword" name="confirmpassword" required >
						</div>
						<button type="submit" name="submit" class="btn-upper btn btn-primary checkout-page-button" id="submit">Registrarse</button>
					</form>
					<span class="checkout-subtitle outer-top-xs">Regístrate hoy y podrás:  </span>
					<div class="checkbox">
						<label class="checkbox">
							Completar rápidamente el proceso de compra.
						</label>
						<label class="checkbox">
							Hacer un seguimiento de tus pedidos fácilmente.
						</label>
						<label class="checkbox">
							Conservar un registro de todas tus compras.
						</label>
					</div>
				</div>	
			</div>
		</div>
		<?php include('includes/brands-slider.php');?>
	</div>
</div>
<?php include('includes/footer.php');?>
<?php include('includes/color-switcher.php');?>
<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/echo.min.js"></script>
<script src="assets/js/jquery.easing-1.3.min.js"></script>
<script src="assets/js/bootstrap-slider.min.js"></script>
<script src="assets/js/jquery.rateit.min.js"></script>
<script type="text/javascript" src="assets/js/lightbox.min.js"></script>
<script src="assets/js/bootstrap-select.min.js"></script>
<script src="assets/js/wow.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>
