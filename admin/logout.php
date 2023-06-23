<?php
session_start();
$_SESSION['alogin']=="";
session_unset();
//session_destroy();
$_SESSION['errmsg']="Ha cerrado la sesión con éxito!";
?>
<script language="javascript">
document.location="index.php";
</script>
