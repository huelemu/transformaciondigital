<?php
// index-debug.php - Versión de debug temporal
require_once 'config.php';

echo "<h2>Debug del Index</h2>";

echo "<h3>1. Antes de requireAuth():</h3>";
echo "isAuthenticated(): " . (isAuthenticated() ? 'TRUE' : 'FALSE') . "<br>";
echo "SessionManager::isValid(): " . (SessionManager::isValid() ? 'TRUE' : 'FALSE') . "<br>";

if (isset($_SESSION['user'])) {
    echo "Usuario en sesión: " . $_SESSION['user']['email'] . "<br>";
} else {
    echo "NO hay usuario en sesión<br>";
}

echo "<h3>2. Test de requireAuth():</h3>";
try {
    // Comentamos requireAuth() para ver si es el problema
    // requireAuth();
    
    if (!isAuthenticated()) {
        echo "❌ requireAuth() redirigiría a login.php<br>";
        echo "<a href='login.php'>Ir a Login</a><br>";
    } else {
        echo "✅ requireAuth() permitiría el acceso<br>";
        echo "<h3>Datos del usuario:</h3>";
        echo "Email: " . $_SESSION['user']['email'] . "<br>";
        echo "Nombre: " . $_SESSION['user']['name'] . "<br>";
        echo "Dominio: " . $_SESSION['user']['domain'] . "<br>";
        echo "<br><a href='logout.php'>Cerrar Sesión</a>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h3>3. Debug completo de SessionManager:</h3>";
echo "<pre>";
print_r(SessionManager::debug());
echo "</pre>";
?>