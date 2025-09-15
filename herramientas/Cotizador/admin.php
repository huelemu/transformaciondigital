<?php
session_start();
$hash = require __DIR__ . '/clave.php'; // O usa '../clave.php' si está en otro directorio

if (!isset($_SESSION['admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clave']) && $_POST['clave'] === $hash) {
        $_SESSION['admin'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        die('<form method="post">Clave: <input type="password" name="clave"><input type="submit" value="Entrar"></form>');
    }
}

$archivo = 'costos.json';
$data = json_decode(file_get_contents($archivo), true);
$costos = $data['costos'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cargar nuevos datos del formulario
    $costos = $_POST['costos'];

    // Convertir valores numéricos correctamente
    foreach ($costos as &$c) {
        $c['costoUSD'] = floatval($c['costoUSD']);
    }

    // Guardar al archivo JSON
    file_put_contents($archivo, json_encode(['costos' => $costos], JSON_PRETTY_PRINT));
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

  <meta charset="UTF-8">
  <title>Administrador de Ítems</title>
  <link rel="stylesheet" href="estilo.css">

  <title>Administrador de Ítems</title>
  
</head>
<body>
 <h1>Administrador de Ítems</h1>
<p style="text-align:right; margin-right: 20px;">
  Bienvenido, Admin | <a href="logout.php" style="color: red; text-decoration: none;">🚪 Cerrar sesión</a>
</p>


  <form method="post">
    <table>
      <thead>
        <tr>
          <th>Tipo Costo</th>
          <th>Recurrencia</th>
          <th>Categoría</th>
          <th>Tipo Producto</th>
          <th>Item</th>
          <th>Costo USD</th>
          <th>Eliminar</th>
        </tr>
      </thead>
      <tbody id="tabla">
        <?php foreach ($costos as $i => $c): ?>
          <tr>
            <td><input type="text" name="costos[<?= $i ?>][tipo_costo]" value="<?= htmlspecialchars($c['tipo_costo']) ?>"></td>
            <td><input type="text" name="costos[<?= $i ?>][recurrencia]" value="<?= htmlspecialchars($c['recurrencia']) ?>"></td>
            <td><input type="text" name="costos[<?= $i ?>][categoria]" value="<?= htmlspecialchars($c['categoria']) ?>"></td>
            <td><input type="text" name="costos[<?= $i ?>][tipo_prod]" value="<?= htmlspecialchars($c['tipo_prod']) ?>"></td>
            <td><input type="text" name="costos[<?= $i ?>][item]" value="<?= htmlspecialchars($c['item']) ?>"></td>
            <td><input type="number" step="0.01" name="costos[<?= $i ?>][costoUSD]" value="<?= htmlspecialchars($c['costoUSD']) ?>"></td>
            <td><button type="button" onclick="eliminarFila(this)">🗑</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <button type="button" onclick="agregarFila()">➕ Agregar Ítem</button>
    <button type="submit" class="boton">💾 Guardar Cambios</button>
  </form>

  <script>
    function agregarFila() {
      const tbody = document.getElementById('tabla');
      const index = tbody.rows.length;
      const fila = document.createElement('tr');
      fila.innerHTML = `
        <td><input type="text" name="costos[${index}][tipo_costo]" /></td>
        <td><input type="text" name="costos[${index}][recurrencia]" /></td>
        <td><input type="text" name="costos[${index}][categoria]" /></td>
        <td><input type="text" name="costos[${index}][tipo_prod]" /></td>
        <td><input type="text" name="costos[${index}][item]" /></td>
        <td><input type="number" step="0.01" name="costos[${index}][costoUSD]" /></td>
        <td><button type="button" onclick="eliminarFila(this)">🗑</button></td>
      `;
      tbody.appendChild(fila);
    }

    function eliminarFila(boton) {
      boton.closest('tr').remove();
    }
  </script>
</body>
</html>
