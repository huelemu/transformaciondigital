<?php
$archivo = 'costos.json';

// Leer el JSON
$datos = json_decode(file_get_contents($archivo), true);
if (!isset($datos['costos'])) $datos['costos'] = [];

// Agregar nuevo item
$datos['costos'][] = [
  'tipo' => $_POST['tipo'],
  'costo' => $_POST['costo'],
  'categoria' => $_POST['categoria'],
  'tipoProd' => $_POST['tipoProd'],
  'cantidad' => null,
  'item' => $_POST['item'],
  'costoUSD' => floatval($_POST['costoUSD'])
];

// Guardar de nuevo
file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT));

// Redirigir a la lista
header('Location: index.php');
exit;
