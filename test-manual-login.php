<?php
require_once 'config.php';
require_once 'session-manager.php';

if ($_POST) {
    echo "<h1>Test de Login Manual</h1>";
    
    $email = $_POST['email'];
    $name = $_POST['name'];
    $picture = $_POST['picture'];
    
    echo "<h2>Datos recibidos:</h2>";
    echo "Email: '$email'<br>";
    echo "Nombre: '$name'<br>";
    echo "Foto: '$picture'<br><br>";
    
    // Verificar dominio paso a paso
    echo "<h2>Verificación de dominio:</h2>";
    echo "isDomainAllowed('$email'): " . (isDomainAllowed($email) ? "✅ SÍ" : "❌ NO") . "<br><br>";
    
    if (isDomainAllowed($email)) {
        echo "<h2>✅ Dominio permitido - Intentando guardar sesión:</h2>";
        
        try {
            SessionManager::start();
            
            $_SESSION['user'] = [
                'email' => $email,
                'name' => $name,
                'picture' => $picture,
                'domain' => substr(strrchr($email, "@"), 1),
                'login_time' => time()
            ];
            
            echo "✅ Sesión guardada<br>";
            echo "✅ isAuthenticated(): " . (isAuthenticated() ? "SÍ" : "NO") . "<br>";
            echo "✅ SessionManager::isValid(): " . (SessionManager::isValid() ? "SÍ" : "NO") . "<br><br>";
            
            echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔗 Ir a index.php</a>";
            
        } catch (Exception $e) {
            echo "❌ Error guardando sesión: " . $e->getMessage() . "<br>";
        }
        
    } else {
        echo "<h2>❌ Dominio NO permitido</h2>";
        $domain = substr(strrchr($email, "@"), 1);
        echo "Tu dominio: '$domain'<br>";
        echo "¿Debería agregarse a la lista de dominios permitidos?<br>";
    }
}
?>