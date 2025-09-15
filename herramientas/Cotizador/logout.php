<?php
session_start();
session_unset();    // Limpia todas las variables de sesión
session_destroy();  // Destruye la sesión

// Redirige al admin para que muestre el login otra vez
header("Location: index.php");
exit;
