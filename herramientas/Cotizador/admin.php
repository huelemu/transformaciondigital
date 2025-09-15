<?php
// Verificar autenticación (opcional)
session_start();

// Leer archivo JSON con los costos
$data = json_decode(file_get_contents('costos.json'), true);
$costos = $data['costos'] ?? [];

// Procesar formulario si se envía
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save_item':
                // Guardar nuevo item o editar existente
                $newItem = [
                    'tipo_costo' => $_POST['tipo_costo'],
                    'recurrencia' => $_POST['recurrencia'],
                    'categoria' => $_POST['categoria'],
                    'tipo_prod' => $_POST['tipo_prod'],
                    'item' => $_POST['item'],
                    'costoUSD' => floatval($_POST['costoUSD']),
                    'margen_custom' => !empty($_POST['margen_custom']) ? intval($_POST['margen_custom']) : null,
                    'notas' => $_POST['notas'] ?? '',
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'activo' => true
                ];
                
                if (isset($_POST['item_id']) && $_POST['item_id'] !== '') {
                    // Editar item existente
                    $itemId = intval($_POST['item_id']);
                    foreach ($costos as $index => $item) {
                        if (isset($item['id']) && $item['id'] == $itemId) {
                            $costos[$index] = array_merge($item, $newItem);
                            $costos[$index]['fecha_modificacion'] = date('Y-m-d H:i:s');
                            break;
                        }
                    }
                } else {
                    // Nuevo item
                    $newItem['id'] = time() + rand(1, 1000); // ID único
                    $costos[] = $newItem;
                }
                
                $data['costos'] = $costos;
                file_put_contents('costos.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                header('Location: admin.php?success=item_saved');
                exit;
                
            case 'delete_item':
                $itemId = intval($_POST['item_id']);
                $costos = array_filter($costos, function($item) use ($itemId) {
                    return !isset($item['id']) || $item['id'] != $itemId;
                });
                
                $data['costos'] = array_values($costos);
                file_put_contents('costos.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                header('Location: admin.php?success=item_deleted');
                exit;
                
            case 'save_margins':
                // Guardar márgenes
                $margins = json_decode($_POST['margins_data'], true);
                foreach ($costos as $index => $item) {
                    $itemId = $item['id'] ?? $index;
                    if (isset($margins[$itemId])) {
                        $costos[$index]['margen_custom'] = intval($margins[$itemId]);
                        $costos[$index]['fecha_modificacion'] = date('Y-m-d H:i:s');
                    }
                }
                
                $data['costos'] = $costos;
                file_put_contents('costos.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                header('Location: admin.php?success=margins_saved');
                exit;

            case 'toggle_item_status':
                // Activar/desactivar item
                $itemId = intval($_POST['item_id']);
                foreach ($costos as $index => $item) {
                    if (isset($item['id']) && $item['id'] == $itemId) {
                        $costos[$index]['activo'] = !($item['activo'] ?? true);
                        $costos[$index]['fecha_modificacion'] = date('Y-m-d H:i:s');
                        break;
                    }
                }
                
                $data['costos'] = $costos;
                file_put_contents('costos.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                header('Location: admin.php?success=status_updated');
                exit;

            case 'bulk_update':
                // Actualización masiva
                $selectedItems = json_decode($_POST['selected_items'], true);
                $updateType = $_POST['bulk_type'];
                $updateValue = $_POST['bulk_value'];

                foreach ($costos as $index => $item) {
                    $itemId = $item['id'] ?? $index;
                    if (in_array($itemId, $selectedItems)) {
                        switch ($updateType) {
                            case 'margin':
                                $costos[$index]['margen_custom'] = intval($updateValue);
                                break;
                            case 'category':
                                $costos[$index]['categoria'] = $updateValue;
                                break;
                            case 'status':
                                $costos[$index]['activo'] = $updateValue === 'active';
                                break;
                        }
                        $costos[$index]['fecha_modificacion'] = date('Y-m-d H:i:s');
                    }
                }

                $data['costos'] = $costos;
                file_put_contents('costos.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                header('Location: admin.php?success=bulk_updated');
                exit;
        }
    }
}

// Calcular estadísticas
$totalItems = count($costos);
$itemsActivos = count(array_filter($costos, function($item) { return $item['activo'] ?? true; }));
$itemsFijos = count(array_filter($costos, function($item) { return $item['tipo_costo'] === 'Fijo'; }));
$itemsVariables = count(array_filter($costos, function($item) { return $item['tipo_costo'] === 'Variable'; }));

$margenGlobal = 50; // Valor por defecto
$margenPromedio = 0;
if ($totalItems > 0) {
    $sumMargenes = 0;
    foreach ($costos as $item) {
        $sumMargenes += $item['margen_custom'] ?? $margenGlobal;
    }
    $margenPromedio = round($sumMargenes / $totalItems, 1);
}

// Obtener categorías únicas
$categorias = array_unique(array_column($costos, 'categoria'));
$tiposProducto = array_unique(array_column($costos, 'tipo_prod'));

// Funciones auxiliares
function obtenerEstadisticasCategoria($costos, $categoria, $margenGlobal) {
    $itemsCategoria = array_filter($costos, function($item) use ($categoria) {
        return $item['categoria'] === $categoria;
    });
    
    if (empty($itemsCategoria)) {
        return [
            'cantidad' => 0,
            'productos' => 0,
            'costoPromedio' => 0,
            'margenPromedio' => 0,
            'precioPromedio' => 0
        ];
    }
    
    $productos = array_unique(array_column($itemsCategoria, 'tipo_prod'));
    $costoTotal = array_sum(array_column($itemsCategoria, 'costoUSD'));
    $costoPromedio = $costoTotal / count($itemsCategoria);
    
    $margenes = [];
    $precios = [];
    foreach ($itemsCategoria as $item) {
        $margen = $item['margen_custom'] ?? $margenGlobal;
        $precio = $item['costoUSD'] / (1 - $margen / 100);
        $margenes[] = $margen;
        $precios[] = $precio;
    }
    
    return [
        'cantidad' => count($itemsCategoria),
        'productos' => count($productos),
        'costoPromedio' => $costoPromedio,
        'margenPromedio' => array_sum($margenes) / count($margenes),
        'precioPromedio' => array_sum($precios) / count($precios)
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Items y Márgenes | SkyTel</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin-styles.css">
    <meta name="description" content="Panel de administración para gestión de items y márgenes del cotizador SkyTel">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>⚙️ Administración - Items y Márgenes</h1>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="generarAnalisisCompleto()" title="Análisis completo (Ctrl+Shift+A)">
                    <span>📊</span> Análisis
                </button>
                <button class="btn btn-secondary" onclick="exportarItems()" title="Exportar items (Ctrl+E)">
                    <span>📤</span> Exportar
                </button>
                <button class="btn btn-secondary" onclick="importarItems()" title="Importar items (Ctrl+I)">
                    <span>📥</span> Importar
                </button>
                <a href="index.php" class="btn btn-primary">← Volver al Cotizador</a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Mostrar mensajes de éxito/error -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" id="success-alert">
                <span class="alert-icon">✅</span>
                <div class="alert-content">
                    <div class="alert-title">Operación exitosa</div>
                    <div class="alert-message">
                        <?php
                        switch ($_GET['success']) {
                            case 'item_saved':
                                echo 'Item guardado correctamente';
                                break;
                            case 'item_deleted':
                                echo 'Item eliminado correctamente';
                                break;
                            case 'margins_saved':
                                echo 'Márgenes guardados correctamente';
                                break;
                            case 'status_updated':
                                echo 'Estado del item actualizado correctamente';
                                break;
                            case 'bulk_updated':
                                echo 'Actualización masiva completada correctamente';
                                break;
                            default:
                                echo 'Operación completada correctamente';
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error" id="error-alert">
                <span class="alert-icon">❌</span>
                <div class="alert-content">
                    <div class="alert-title">Error</div>
                    <div class="alert-message">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Estadísticas Dashboard -->
        <div class="stats-grid">
            <div class="stat-card stat-items">
                <div class="stat-value"><?= $totalItems ?></div>
                <div class="stat-label">Total Items</div>
                <div class="stat-sublabel"><?= $itemsActivos ?> activos</div>
            </div>
            <div class="stat-card stat-fijos">
                <div class="stat-value"><?= $itemsFijos ?></div>
                <div class="stat-label">Items Fijos</div>
                <div class="stat-sublabel"><?= round(($itemsFijos / max($totalItems, 1)) * 100, 1) ?>%</div>
            </div>
            <div class="stat-card stat-variables">
                <div class="stat-value"><?= $itemsVariables ?></div>
                <div class="stat-label">Items Variables</div>
                <div class="stat-sublabel"><?= round(($itemsVariables / max($totalItems, 1)) * 100, 1) ?>%</div>
            </div>
            <div class="stat-card stat-margen">
                <div class="stat-value"><?= $margenPromedio ?>%</div>
                <div class="stat-label">Margen Promedio</div>
                <div class="stat-sublabel">Global: <?= $margenGlobal ?>%</div>
            </div>
        </div>

        <!-- Pestañas de Navegación -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('items')" data-tab="items">
                📦 Gestión de Items
            </button>
            <button class="tab" onclick="switchTab('margenes')" data-tab="margenes">
                📊 Márgenes por Item
            </button>
            <button class="tab" onclick="switchTab('categorias')" data-tab="categorias">
                🏷️ Análisis por Categorías
            </button>
            <button class="tab" onclick="switchTab('configuracion')" data-tab="configuracion">
                ⚙️ Configuración
            </button>
        </div>

        <!-- Tab: Gestión de Items -->
        <div id="items-tab" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestión de Items</h3>
                    <div class="header-actions">
                        <button class="btn btn-secondary" onclick="toggleBulkActions()" id="bulk-toggle">
                            <span>☑️</span> Selección Múltiple
                        </button>
                        <button class="btn btn-primary" onclick="openModal('item-modal')" title="Nuevo item (Ctrl+N)">
                            <span>➕</span> Nuevo Item
                        </button>
                    </div>
                </div>

                <!-- Acciones masivas (ocultas por defecto) -->
                <div class="bulk-actions" id="bulk-actions" style="display: none;">
                    <div class="bulk-actions-header">
                        <span id="selected-count">0 items seleccionados</span>
                        <div class="bulk-buttons">
                            <select id="bulk-action-type">
                                <option value="">Seleccionar acción...</option>
                                <option value="margin">Cambiar margen</option>
                                <option value="category">Cambiar categoría</option>
                                <option value="status">Cambiar estado</option>
                                <option value="delete">Eliminar seleccionados</option>
                            </select>
                            <input type="text" id="bulk-action-value" placeholder="Nuevo valor..." style="display: none;">
                            <button class="btn btn-warning" onclick="executeBulkAction()">Aplicar</button>
                            <button class="btn btn-secondary" onclick="clearSelection()">Limpiar</button>
                        </div>
                    </div>
                </div>

                <!-- Filtros Avanzados -->
                <div class="filters-container">
                    <div class="filters-header">
                        <h4 class="filters-title">🔍 Filtros de Búsqueda</h4>
                        <button class="filters-toggle" onclick="toggleFilters()">
                            <span id="filter-toggle-text">Ocultar</span>
                        </button>
                    </div>
                    <div class="filters-grid" id="filters-grid">
                        <div class="filter-group">
                            <label>Búsqueda general</label>
                            <div class="search-container">
                                <span class="search-icon">🔍</span>
                                <input type="text" class="search-input" id="search-items" placeholder="Buscar en todos los campos...">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Tipo de Costo</label>
                            <select id="filter-tipo" class="filter-select">
                                <option value="">Todos los tipos</option>
                                <option value="Fijo">Fijo</option>
                                <option value="Variable">Variable</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Categoría</label>
                            <select id="filter-categoria" class="filter-select">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= htmlspecialchars($categoria) ?>">
                                        <?= htmlspecialchars($categoria) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Recurrencia</label>
                            <select id="filter-recurrencia" class="filter-select">
                                <option value="">Todas las recurrencias</option>
                                <option value="Mensual">Mensual</option>
                                <option value="Unico">Único</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Estado</label>
                            <select id="filter-estado" class="filter-select">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activos</option>
                                <option value="inactivo">Inactivos</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Rango de Costo</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="number" id="filter-costo-min" placeholder="Mín" step="0.01">
                                <input type="number" id="filter-costo-max" placeholder="Máx" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="filter-clear">
                        <span class="filters-count" id="filters-count">
                            Mostrando <?= $totalItems ?> de <?= $totalItems ?> items
                        </span>
                        <button class="btn-clear-filters" onclick="clearFilters()">
                            🗑️ Limpiar Filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla de Items -->
                <div class="admin-table">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th class="bulk-select-header" style="display: none;">
                                        <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
                                    </th>
                                    <th onclick="sortTable(1)">Tipo <span class="sort-indicator">↕️</span></th>
                                    <th onclick="sortTable(2)">Categoría <span class="sort-indicator">↕️</span></th>
                                    <th onclick="sortTable(3)">Producto <span class="sort-indicator">↕️</span></th>
                                    <th onclick="sortTable(4)">Item <span class="sort-indicator">↕️</span></th>
                                    <th onclick="sortTable(5)">Costo USD <span class="sort-indicator">↕️</span></th>
                                    <th onclick="sortTable(6)">Margen % <span class="sort-indicator">↕️</span></th>
                                    <th onclick="sortTable(7)">Precio Venta <span class="sort-indicator">↕️</span></th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="items-table">
                                <?php foreach ($costos as $index => $item): ?>
                                    <?php
                                    $itemId = $item['id'] ?? $index;
                                    $margen = $item['margen_custom'] ?? $margenGlobal;
                                    $precioVenta = $item['costoUSD'] > 0 ? $item['costoUSD'] / (1 - $margen / 100) : 0;
                                    $activo = $item['activo'] ?? true;
                                    ?>
                                    <tr data-id="<?= $itemId ?>" class="item-row <?= !$activo ? 'item-inactive' : '' ?>" 
                                        data-tipo="<?= htmlspecialchars($item['tipo_costo']) ?>"
                                        data-categoria="<?= htmlspecialchars($item['categoria']) ?>"
                                        data-recurrencia="<?= htmlspecialchars($item['recurrencia']) ?>"
                                        data-costo="<?= $item['costoUSD'] ?>"
                                        data-activo="<?= $activo ? 'true' : 'false' ?>">
                                        
                                        <td class="bulk-select-cell" style="display: none;">
                                            <input type="checkbox" class="item-checkbox" value="<?= $itemId ?>">
                                        </td>
                                        
                                        <td>
                                            <span class="tag tag-<?= strtolower($item['tipo_costo']) ?>">
                                                <?= htmlspecialchars($item['tipo_costo']) ?>
                                            </span>
                                            <small class="tag tag-<?= strtolower($item['recurrencia']) ?>" style="margin-left: 0.5rem;">
                                                <?= htmlspecialchars($item['recurrencia']) ?>
                                            </small>
                                        </td>
                                        
                                        <td><?= htmlspecialchars($item['categoria']) ?></td>
                                        
                                        <td><?= htmlspecialchars($item['tipo_prod']) ?></td>
                                        
                                        <td class="tooltip-advanced" data-tooltip="<?= htmlspecialchars($item['item']) ?>">
                                            <span class="text-truncate" style="max-width: 300px; display: inline-block;">
                                                <?= htmlspecialchars($item['item']) ?>
                                            </span>
                                            <?php if (!empty($item['notas'])): ?>
                                                <br><small class="text-secondary">📝 <?= htmlspecialchars(substr($item['notas'], 0, 50)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="font-mono">$<?= number_format($item['costoUSD'], 4) ?></td>
                                        
                                        <td>
                                            <span class="margin-indicator <?= $item['margen_custom'] ? 'positive' : 'neutral' ?>">
                                                <?= $margen ?>%
                                            </span>
                                            <?php if ($item['margen_custom']): ?>
                                                <small style="display: block; color: var(--text-secondary);">Personalizado</small>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="font-mono money positive">$<?= number_format($precioVenta, 4) ?></td>
                                        
                                        <td>
                                            <span class="status-indicator <?= $activo ? 'status-active' : 'status-inactive' ?>">
                                                <?= $activo ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                            <?php if (isset($item['fecha_modificacion'])): ?>
                                                <br><small class="text-secondary">
                                                    <?= date('d/m/Y', strtotime($item['fecha_modificacion'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="table-actions-cell">
                                            <button class="btn action-btn-edit" onclick="editarItem(<?= $itemId ?>)" title="Editar item">
                                                ✏️
                                            </button>
                                            <button class="btn action-btn-duplicate" onclick="duplicarItem(<?= $itemId ?>)" title="Duplicar item">
                                                📋
                                            </button>
                                            <button class="btn <?= $activo ? 'action-btn-warning' : 'action-btn-success' ?>" 
                                                    onclick="toggleItemStatus(<?= $itemId ?>)" 
                                                    title="<?= $activo ? 'Desactivar' : 'Activar' ?> item">
                                                <?= $activo ? '⏸️' : '▶️' ?>
                                            </button>
                                            <button class="btn action-btn-delete" onclick="eliminarItem(<?= $itemId ?>)" title="Eliminar item">
                                                🗑️
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Márgenes por Item -->
        <div id="margenes-tab" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestión de Márgenes por Item</h3>
                    <div class="header-actions">
                        <button class="btn btn-success" onclick="guardarMargenes()" title="Guardar márgenes (Ctrl+S)">
                            <span>💾</span> Guardar Márgenes
                        </button>
                        <button class="btn btn-secondary" onclick="resetearMargenes()">
                            <span>🔄</span> Resetear Cambios
                        </button>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="margen-global">Margen Global por Defecto</label>
                        <div class="input-with-icon">
                            <span class="icon">%</span>
                            <input type="number" id="margen-global" value="<?= $margenGlobal ?>" min="0" max="99" step="1">
                        </div>
                        <span class="help-text">Este margen se aplica a items sin margen personalizado</span>
                    </div>
                    <div class="form-group">
                        <label for="margen-categoria">Aplicar por Categoría</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select id="categoria-margen-select">
                                <option value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= htmlspecialchars($categoria) ?>">
                                        <?= htmlspecialchars($categoria) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" id="margen-categoria-valor" placeholder="%" min="0" max="99" step="1">
                            <button class="btn btn-secondary" onclick="aplicarMargenCategoria()">Aplicar</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button class="btn btn-warning" onclick="aplicarMargenGlobal()">
                            <span>🌐</span> Aplicar Margen Global a Todos
                        </button>
                    </div>
                </div>

                <!-- Tabla de Márgenes -->
                <div class="admin-table">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Categoría</th>
                                    <th>Costo USD</th>
                                    <th>Margen Actual %</th>
                                    <th>Nuevo Margen %</th>
                                    <th>Precio Venta</th>
                                    <th>Diferencia vs Global</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="margenes-table">
                                <?php foreach ($costos as $index => $item): ?>
                                    <?php
                                    $itemId = $item['id'] ?? $index;
                                    $margen = $item['margen_custom'] ?? $margenGlobal;
                                    $precioVenta = $item['costoUSD'] > 0 ? $item['costoUSD'] / (1 - $margen / 100) : 0;
                                    $diferencia = $margen - $margenGlobal;
                                    $activo = $item['activo'] ?? true;
                                    ?>
                                    <tr class="<?= !$activo ? 'item-inactive' : '' ?>">
                                        <td class="tooltip-advanced" data-tooltip="<?= htmlspecialchars($item['item']) ?>">
                                            <span class="text-truncate" style="max-width: 200px; display: inline-block;">
                                                <?= htmlspecialchars($item['item']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="tag tag-<?= strtolower(str_replace(' ', '-', $item['categoria'])) ?>">
                                                <?= htmlspecialchars($item['categoria']) ?>
                                            </span>
                                        </td>
                                        <td class="font-mono">$<?= number_format($item['costoUSD'], 4) ?></td>
                                        <td class="font-mono"><?= $margen ?>%</td>
                                        <td>
                                            <div class="margin-control">
                                                <input type="number" 
                                                       class="margin-input" 
                                                       value="<?= $margen ?>" 
                                                       min="0" 
                                                       max="99" 
                                                       step="1" 
                                                       data-id="<?= $itemId ?>" 
                                                       data-original="<?= $margen ?>"
                                                       <?= !$activo ? 'disabled' : '' ?>
                                                       onchange="actualizarMargenItem(<?= $itemId ?>, this.value)">
                                                <span class="margin-unit">%</span>
                                            </div>
                                        </td>
                                        <td class="font-mono precio-venta-<?= $itemId ?>">$<?= number_format($precioVenta, 4) ?></td>
                                        <td>
                                            <span class="margin-indicator diferencia-<?= $itemId ?> <?= $diferencia > 0 ? 'positive' : ($diferencia < 0 ? 'negative' : 'neutral') ?>">
                                                <?= $diferencia > 0 ? '+' : '' ?><?= $diferencia ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-indicator <?= $item['margen_custom'] ? 'status-warning' : 'status-active' ?>">
                                                <?= $item['margen_custom'] ? 'Personalizado' : 'Global' ?>
                                            </span>
                                            <?php if (!$activo): ?>
                                                <br><small class="status-indicator status-inactive">Inactivo</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Análisis por Categorías -->
        <div id="categorias-tab" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Análisis por Categorías</h3>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openModal('categoria-modal')">
                            <span>➕</span> Nueva Categoría
                        </button>
                        <button class="btn btn-secondary" onclick="exportarAnalisisCategorias()">
                            <span>📊</span> Exportar Análisis
                        </button>
                    </div>
                </div>

                <!-- Gráfico de distribución (placeholder) -->
                <div class="chart-container" style="margin-bottom: 2rem; padding: 1rem; background: var(--background); border-radius: 8px;">
                    <h4>Distribución por Categorías</h4>
                    <div id="categorias-chart" style="height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-secondary);">
                        📊 Gráfico de distribución por categorías<br>
                        <small>(Se puede implementar con Chart.js o similar)</small>
                    </div>
                </div>

                <!-- Tabla de Análisis por Categorías -->
                <div class="admin-table">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th>Productos Únicos</th>
                                    <th>Total Items</th>
                                    <th>Items Activos</th>
                                    <th>Costo Promedio</th>
                                    <th>Margen Promedio</th>
                                    <th>Precio Promedio</th>
                                    <th>Participación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="categorias-table">
                                <?php
                                $categoriaStats = [];
                                foreach ($costos as $item) {
                                    $cat = $item['categoria'];
                                    if (!isset($categoriaStats[$cat])) {
                                        $categoriaStats[$cat] = [
                                            'items' => [],
                                            'productos' => [],
                                            'activos' => 0
                                        ];
                                    }
                                    $categoriaStats[$cat]['items'][] = $item;
                                    $categoriaStats[$cat]['productos'][$item['tipo_prod']] = true;
                                    if ($item['activo'] ?? true) {
                                        $categoriaStats[$cat]['activos']++;
                                    }
                                }

                                foreach ($categoriaStats as $catName => $stats):
                                    $itemsCount = count($stats['items']);
                                    $productosCount = count($stats['productos']);
                                    $activosCount = $stats['activos'];
                                    $participacion = ($itemsCount / max($totalItems, 1)) * 100;
                                    
                                    $costoPromedio = 0;
                                    $margenes = [];
                                    $precios = [];
                                    
                                    foreach ($stats['items'] as $item) {
                                        $costoPromedio += $item['costoUSD'];
                                        $margen = $item['margen_custom'] ?? $margenGlobal;
                                        $precio = $item['costoUSD'] > 0 ? $item['costoUSD'] / (1 - $margen / 100) : 0;
                                        $margenes[] = $margen;
                                        $precios[] = $precio;
                                    }
                                    
                                    $costoPromedio = $costoPromedio / max($itemsCount, 1);
                                    $margenPromedioCat = array_sum($margenes) / max(count($margenes), 1);
                                    $precioPromedio = array_sum($precios) / max(count($precios), 1);
                                ?>
                                    <tr data-categoria="<?= htmlspecialchars($catName) ?>">
                                        <td>
                                            <span class="tag tag-<?= strtolower(str_replace(' ', '-', $catName)) ?>">
                                                <?= htmlspecialchars($catName) ?>
                                            </span>
                                        </td>
                                        <td class="text-center"><?= $productosCount ?></td>
                                        <td class="text-center">
                                            <?= $itemsCount ?>
                                            <?php if ($activosCount !== $itemsCount): ?>
                                                <br><small class="text-secondary"><?= $activosCount ?> activos</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $activosCount ?></td>
                                        <td class="font-mono">$<?= number_format($costoPromedio, 2) ?></td>
                                        <td class="text-center"><?= number_format($margenPromedioCat, 1) ?>%</td>
                                        <td class="font-mono">$<?= number_format($precioPromedio, 2) ?></td>
                                        <td>
                                            <div class="progress-container">
                                                <div class="progress-label">
                                                    <span><?= number_format($participacion, 1) ?>%</span>
                                                </div>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?= $participacion ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="table-actions-cell">
                                            <button class="btn action-btn-edit" onclick="generarReporteCategoria('<?= htmlspecialchars($catName) ?>')" title="Generar reporte">
                                                📊
                                            </button>
                                            <button class="btn action-btn-warning" onclick="editarCategoria('<?= htmlspecialchars($catName) ?>')" title="Editar categoría">
                                                ✏️
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Configuración -->
        <div id="configuracion-tab" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Configuración del Sistema</h3>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Configuración de Márgenes</label>
                        <div class="alert alert-info">
                            <span class="alert-icon">ℹ️</span>
                            <div class="alert-content">
                                <div class="alert-message">
                                    El margen global se aplica automáticamente a todos los items que no tengan un margen personalizado.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Backup y Restauración</label>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <button class="btn btn-secondary" onclick="crearBackup()" title="Crear backup (Ctrl+B)">
                                <span>💾</span> Crear Backup
                            </button>
                            <button class="btn btn-secondary" onclick="restaurarBackup()">
                                <span>📥</span> Restaurar Backup
                            </button>
                            <button class="btn btn-warning" onclick="limpiarDatos()">
                                <span>🗑️</span> Limpiar Todos los Datos
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Herramientas de Desarrollo</label>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <button class="btn btn-secondary" onclick="validarIntegridadDatos()">
                                <span>🔍</span> Validar Integridad
                            </button>
                            <button class="btn btn-secondary" onclick="regenerarIds()">
                                <span>🔄</span> Regenerar IDs
                            </button>
                            <button class="btn btn-secondary" onclick="mostrarInformacionSistema()">
                                <span>📋</span> Info del Sistema
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Estadísticas del Sistema</label>
                        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
                            <div class="stat-card">
                                <div class="stat-value"><?= number_format(filesize('costos.json') / 1024, 1) ?> KB</div>
                                <div class="stat-label">Tamaño del Archivo</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value"><?= date('d/m/Y H:i', filemtime('costos.json')) ?></div>
                                <div class="stat-label">Última Modificación</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value"><?= count($categorias) ?></div>
                                <div class="stat-label">Categorías Únicas</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value"><?= count($tiposProducto) ?></div>
                                <div class="stat-label">Tipos de Producto</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Nuevo/Editar Item -->
    <div id="item-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="item-modal-title">Nuevo Item</h3>
                <button class="close-btn" onclick="closeModal('item-modal')">&times;</button>
            </div>

            <div class="modal-body">
                <form id="item-form" method="post">
                    <input type="hidden" name="action" value="save_item">
                    <input type="hidden" name="item_id" id="edit-item-id">
                    
                    <div class="form-grid">
                        <div class="form-group required">
                            <label for="tipo_costo">
                                <span>💼</span> Tipo de Costo
                            </label>
                            <select name="tipo_costo" id="tipo_costo" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="Fijo">💰 Fijo - Costo constante mensual</option>
                                <option value="Variable">📊 Variable - Depende del uso</option>
                            </select>
                            <span class="help-text">Fijo: costo constante mensual. Variable: depende del uso o cantidad.</span>
                        </div>

                        <div class="form-group required">
                            <label for="recurrencia">
                                <span>🔄</span> Recurrencia
                            </label>
                            <select name="recurrencia" id="recurrencia" required>
                                <option value="">Seleccionar recurrencia...</option>
                                <option value="Mensual">📅 Mensual - Se cobra cada mes</option>
                                <option value="Unico">⚡ Único - Costo de una sola vez</option>
                            </select>
                        </div>

                        <div class="form-group required">
                            <label for="categoria">
                                <span>🏷️</span> Categoría
                            </label>
                            <select name="categoria" id="categoria" required>
                                <option value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= htmlspecialchars($categoria) ?>">
                                        <?= htmlspecialchars($categoria) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="Plataforma">🏗️ Plataforma</option>
                                <option value="Canal">📡 Canal</option>
                                <option value="Servicios">🛠️ Servicios</option>
                                <option value="Hardware">💻 Hardware</option>
                                <option value="Software">💿 Software</option>
                                <option value="Licencias">📜 Licencias</option>
                                <option value="Integracion">🔗 Integración</option>
                            </select>
                        </div>

                        <div class="form-group required">
                            <label for="tipo_prod">
                                <span>🎯</span> Tipo de Producto
                            </label>
                            <input type="text" name="tipo_prod" id="tipo_prod" required 
                                   placeholder="Ej: Omnicanalidad, WhatsApp, Email, IA"
                                   list="tipos-producto-list">
                            <datalist id="tipos-producto-list">
                                <option value="Omnicanalidad">
                                <option value="WhatsApp">
                                <option value="Email">
                                <option value="SMS">
                                <option value="IA">
                                <option value="CiberSecurity">
                                <option value="VoIP">
                                <option value="Chat">
                                <option value="Video">
                            </datalist>
                            <span class="help-text">Categoría específica del producto o servicio</span>
                        </div>

                        <div class="form-group required full-width">
                            <label for="item">
                                <span>📝</span> Nombre del Item
                            </label>
                            <input type="text" name="item" id="item" required 
                                   placeholder="Descripción detallada y específica del item">
                            <span class="help-text">Descripción completa que identifique claramente el item</span>
                        </div>

                        <div class="form-group required">
                            <label for="costoUSD">
                                <span>💵</span> Costo USD
                            </label>
                            <div class="input-with-icon">
                                <span class="icon">$</span>
                                <input type="number" name="costoUSD" id="costoUSD" required 
                                       step="0.0001" min="0" placeholder="0.0000">
                            </div>
                            <span class="help-text">Costo en dólares americanos (hasta 4 decimales)</span>
                        </div>

                        <div class="form-group">
                            <label for="margen_custom">
                                <span>📈</span> Margen Personalizado %
                            </label>
                            <div class="input-with-icon">
                                <span class="icon">%</span>
                                <input type="number" name="margen_custom" id="margen_custom" 
                                       step="1" min="0" max="99" placeholder="Opcional">
                            </div>
                            <span class="help-text">Dejar vacío para usar margen global (<?= $margenGlobal ?>%)</span>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="notas">
                            <span>📋</span> Notas / Observaciones
                        </label>
                        <textarea name="notas" id="notas" rows="3" 
                                  placeholder="Información adicional, especificaciones técnicas, condiciones especiales..."></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('item-modal')">
                    Cancelar
                </button>
                <button type="submit" form="item-form" class="btn btn-primary">
                    <span>💾</span> Guardar Item
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Nueva Categoría -->
    <div id="categoria-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Nueva Categoría</h3>
                <button class="close-btn" onclick="closeModal('categoria-modal')">&times;</button>
            </div>

            <div class="modal-body">
                <form id="categoria-form">
                    <div class="form-group required">
                        <label for="nueva_categoria">Nombre de la Categoría</label>
                        <input type="text" id="nueva_categoria" required 
                               placeholder="Ej: Inteligencia Artificial, Cloud Computing">
                    </div>

                    <div class="form-group">
                        <label for="categoria_descripcion">Descripción</label>
                        <textarea id="categoria_descripcion" rows="3" 
                                  placeholder="Descripción detallada de la categoría y sus características..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="categoria_margen">Margen por Defecto %</label>
                        <input type="number" id="categoria_margen" step="1" min="0" max="99" 
                               placeholder="50" value="<?= $margenGlobal ?>">
                    </div>

                    <div class="form-group">
                        <label for="categoria_color">Color Identificativo</label>
                        <input type="color" id="categoria_color" value="#2563eb">
                        <span class="help-text">Color para identificar visualmente la categoría</span>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('categoria-modal')">
                    Cancelar
                </button>
                <button type="submit" form="categoria-form" class="btn btn-primary">
                    <span>➕</span> Crear Categoría
                </button>
            </div>
        </div>
    </div>

    <!-- Formularios ocultos para acciones AJAX -->
    <form id="delete-form" method="post" style="display: none;">
        <input type="hidden" name="action" value="delete_item">
        <input type="hidden" name="item_id" id="delete-item-id">
    </form>

    <form id="margins-form" method="post" style="display: none;">
        <input type="hidden" name="action" value="save_margins">
        <input type="hidden" name="margins_data" id="margins-data">
    </form>

    <form id="toggle-status-form" method="post" style="display: none;">
        <input type="hidden" name="action" value="toggle_item_status">
        <input type="hidden" name="item_id" id="toggle-item-id">
    </form>

    <form id="bulk-action-form" method="post" style="display: none;">
        <input type="hidden" name="action" value="bulk_update">
        <input type="hidden" name="selected_items" id="bulk-selected-items">
        <input type="hidden" name="bulk_type" id="bulk-type">
        <input type="hidden" name="bulk_value" id="bulk-value">
    </form>

    <!-- Scripts -->
    <script src="admin.js"></script>
    <script>
        // Funciones específicas de PHP
        function toggleItemStatus(id) {
            if (confirm('¿Cambiar el estado de este item?')) {
                document.getElementById('toggle-item-id').value = id;
                document.getElementById('toggle-status-form').submit();
            }
        }

        function limpiarDatos() {
            if (confirm('⚠️ ADVERTENCIA ⚠️\n\nEsto eliminará TODOS los datos de forma permanente.\n\n¿Estás absolutamente seguro?')) {
                if (confirm('Esta acción NO se puede deshacer.\n\n¿Continuar?')) {
                    // Implementar limpieza de datos
                    alert('Funcionalidad de limpieza no implementada por seguridad');
                }
            }
        }

        function validarIntegridadDatos() {
            // Implementar validación
            let errores = [];
            let items = <?= json_encode($costos) ?>;
            
            items.forEach((item, index) => {
                if (!item.item || item.item.trim() === '') {
                    errores.push(`Item ${index + 1}: Nombre vacío`);
                }
                if (!item.costoUSD || item.costoUSD < 0) {
                    errores.push(`Item ${index + 1}: Costo inválido`);
                }
            });

            if (errores.length > 0) {
                alert('Errores encontrados:\n\n' + errores.join('\n'));
            } else {
                mostrarNotificacion('✅ Todos los datos son válidos', 'success');
            }
        }

        function regenerarIds() {
            if (confirm('¿Regenerar todos los IDs? Esto puede afectar referencias externas.')) {
                alert('Funcionalidad no implementada');
            }
        }

        function mostrarInformacionSistema() {
            const info = {
                'Total de items': <?= $totalItems ?>,
                'Items activos': <?= $itemsActivos ?>,
                'Categorías': <?= count($categorias) ?>,
                'Tipos de producto': <?= count($tiposProducto) ?>,
                'Tamaño del archivo': '<?= number_format(filesize("costos.json") / 1024, 1) ?> KB',
                'Última modificación': '<?= date("d/m/Y H:i", filemtime("costos.json")) ?>'
            };

            let mensaje = 'INFORMACIÓN DEL SISTEMA\n\n';
            Object.entries(info).forEach(([key, value]) => {
                mensaje += `${key}: ${value}\n`;
            });

            alert(mensaje);
        }

        // Ocultar alertas automáticamente
        setTimeout(function() {
            const alerts = document.querySelectorAll('#success-alert, #error-alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);

        // Inicializar tooltips para elementos con data-tooltip
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar funcionalidad adicional específica de PHP aquí
        });
    </script>
</body>
</html>