<?php
require_once 'config.php';
require_once 'session-manager.php';
require_once 'vendor/autoload.php';

echo "<h1>Debug para Cuenta Real</h1>";

// 1. Mostrar dominios permitidos
echo "<h2>1. Dominios Permitidos:</h2>";
global $allowed_domains;
echo "<ul>";
foreach ($allowed_domains as $domain) {
    echo "<li><strong>$domain</strong></li>";
}
echo "</ul>";

// 2. Test manual de tu email
echo "<h2>2. Test Manual de Email:</h2>";
echo "<form method='POST'>";
echo "Tu email: <input type='email' name='test_email' value='" . (isset($_POST['test_email']) ? $_POST['test_email'] : '') . "' required>";
echo "<button type='submit'>Verificar Dominio</button>";
echo "</form>";

if (isset($_POST['test_email'])) {
    $email = trim($_POST['test_email']);
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Email a verificar:</strong> '$email'<br>";
    echo "<strong>Longitud:</strong> " . strlen($email) . " caracteres<br>";
    
    // Mostrar cada caracter para detectar espacios ocultos
    echo "<strong>Caracteres:</strong> ";
    for ($i = 0; $i < strlen($email); $i++) {
        $char = $email[$i];
        echo "[$char]";
    }
    echo "<br>";
    
    // Validar formato
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "✅ <strong>Formato de email válido</strong><br>";
        
        // Extraer dominio
        $domain = substr(strrchr($email, "@"), 1);
        echo "<strong>Dominio extraído:</strong> '$domain'<br>";
        echo "<strong>Longitud del dominio:</strong> " . strlen($domain) . " caracteres<br>";
        
        // Verificar si está en la lista
        $is_allowed = in_array($domain, $allowed_domains);
        echo "<strong>¿Está en lista permitida?</strong> " . ($is_allowed ? "✅ SÍ" : "❌ NO") . "<br>";
        
        // Función original
        $original_check = isDomainAllowed($email);
        echo "<strong>isDomainAllowed():</strong> " . ($original_check ? "✅ SÍ" : "❌ NO") . "<br>";
        
        // Comparación exacta con cada dominio permitido
        echo "<h3>Comparación exacta:</h3>";
        foreach ($allowed_domains as $allowed) {
            $match = ($domain === $allowed);
            echo "- '$domain' === '$allowed': " . ($match ? "✅ MATCH" : "❌ NO MATCH") . "<br>";
        }
        
    } else {
        echo "❌ <strong>Formato de email inválido</strong><br>";
    }
    echo "</div>";
}

// 3. Test de Google OAuth con tu cuenta
echo "<h2>3. Información de OAuth:</h2>";
echo "<p>Para obtener información real de tu cuenta, necesitamos capturar los datos que Google devuelve.</p>";
echo "<a href='debug-oauth-start.php' style='background: #4285f4; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔗 Iniciar Debug OAuth</a>";
?>
