<?php
/**
 * Script de migración para agregar campo "grupo" a items existentes
 * Ejecutar una sola vez para actualizar la estructura de datos
 */

echo "🚀 Iniciando migración para agregar campo 'grupo'...\n\n";

// 1. Crear backup del archivo actual
$archivoOriginal = 'costos.json';
$archivoBackup = 'costos_backup_' . date('Y-m-d_H-i-s') . '.json';

if (!file_exists($archivoOriginal)) {
    die("❌ Error: No se encontró el archivo costos.json\n");
}

// Crear backup
if (copy($archivoOriginal, $archivoBackup)) {
    echo "✅ Backup creado: {$archivoBackup}\n";
} else {
    die("❌ Error: No se pudo crear el backup\n");
}

// 2. Leer datos actuales
$data = json_decode(file_get_contents($archivoOriginal), true);

if (!$data || !isset($data['costos'])) {
    die("❌ Error: Formato de archivo JSON inválido\n");
}

$costos = $data['costos'];
$totalItems = count($costos);
echo "📊 Items encontrados: {$totalItems}\n\n";

// 3. Mapeo automático de grupos basado en categorías existentes
$mapeoGrupos = [
    'Plataforma' => 'Setup Inicial',
    'Canal' => 'Canales de Comunicación',
    'Integración' => 'Integraciones',
    'Desarrollo' => 'Desarrollo Personalizado',
    'Soporte' => 'Soporte y Mantenimiento',
    'Hosting' => 'Infraestructura',
    'Licencias' => 'Licencias y Software'
];

// 4. Migrar cada item
$itemsMigrados = 0;
$gruposCreados = [];

foreach ($costos as $index => &$item) {
    // Si ya tiene grupo, no hacer nada
    if (isset($item['grupo'])) {
        continue;
    }
    
    // Asignar grupo basado en categoría
    $categoria = $item['categoria'] ?? '';
    $grupo = $mapeoGrupos[$categoria] ?? 'General';
    
    // Asignar grupo específico según tipo de costo
    if ($item['tipo_costo'] === 'Fijo') {
        if (strpos(strtolower($item['item']), 'setup') !== false || 
            strpos(strtolower($item['item']), 'inicial') !== false) {
            $grupo = 'Setup Inicial';
        } elseif ($categoria === 'Plataforma') {
            $grupo = 'Plataforma Base';
        }
    } elseif ($item['tipo_costo'] === 'Variable') {
        if ($categoria === 'Canal') {
            $grupo = 'Costos Variables';
        }
    }
    
    // Agregar campo grupo
    $item['grupo'] = $grupo;
    $item['fecha_migracion'] = date('Y-m-d H:i:s');
    
    $itemsMigrados++;
    $gruposCreados[$grupo] = ($gruposCreados[$grupo] ?? 0) + 1;
    
    echo "✅ Item #{$index}: '{$item['item']}' → Grupo: '{$grupo}'\n";
}

// 5. Guardar archivo actualizado
$data['costos'] = $costos;
$data['migracion'] = [
    'fecha' => date('Y-m-d H:i:s'),
    'version' => '2.0',
    'items_migrados' => $itemsMigrados,
    'backup_archivo' => $archivoBackup
];

if (file_put_contents($archivoOriginal, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo "\n✅ Migración completada exitosamente!\n";
    echo "📄 Archivo actualizado: {$archivoOriginal}\n";
    echo "📋 Items migrados: {$itemsMigrados}/{$totalItems}\n\n";
    
    echo "📊 Grupos creados:\n";
    foreach ($gruposCreados as $grupo => $cantidad) {
        echo "   • {$grupo}: {$cantidad} items\n";
    }
    
    echo "\n🎯 Próximos pasos:\n";
    echo "   1. Actualizar admin.php con el nuevo formulario\n";
    echo "   2. Actualizar admin.js con gestión de grupos\n";
    echo "   3. Probar la funcionalidad en el admin\n";
    echo "   4. Actualizar index.php para mostrar grupos (opcional)\n\n";
    
} else {
    echo "\n❌ Error: No se pudo guardar el archivo migrado\n";
    echo "💡 Restaurar desde backup: {$archivoBackup}\n";
}

echo "🏁 Migración finalizada.\n";
?>