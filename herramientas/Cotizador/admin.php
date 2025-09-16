<?php
// admin.php - Versión corregida sin duplicaciones de JavaScript
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
                    'grupo' => $_POST['grupo'] ?? 'General', // NUEVO CAMPO
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
                    $newItem['id'] = time() + rand(1, 1000);
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
                
            case 'bulk_update_grupo':
                // Actualización masiva de grupos
                $selectedItems = json_decode($_POST['selected_items'], true);
                $nuevoGrupo = $_POST['nuevo_grupo'];
                $itemsActualizados = 0;

                foreach ($costos as $index => $item) {
                    $itemId = $item['id'] ?? $index;
                    if (in_array($itemId, $selectedItems)) {
                        $costos[$index]['grupo'] = $nuevoGrupo;
                        $costos[$index]['fecha_modificacion'] = date('Y-m-d H:i:s');
                        $itemsActualizados++;
                    }
                }

                $data['costos'] = $costos;
                file_put_contents('costos.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                header("Location: admin.php?success=bulk_updated&count={$itemsActualizados}");
                exit;
        }
    }
}

// Obtener listas únicas para filtros y selects
$categorias = array_unique(array_filter(array_column($costos, 'categoria')));
$grupos = array_unique(array_filter(array_column($costos, 'grupo')));
$tiposProducto = array_unique(array_filter(array_column($costos, 'tipo_prod')));

// Calcular estadísticas principales
$totalItems = count($costos);
$itemsActivos = count(array_filter($costos, function($item) { 
    return $item['activo'] ?? true; 
}));

// Calcular margen promedio global
$margenGlobal = 50;
$margenPromedio = 0;
if ($totalItems > 0) {
    $sumaMaxenes = 0;
    foreach ($costos as $item) {
        $margen = $item['margen_custom'] ?? $margenGlobal;
        $sumaMaxenes += $margen;
    }
    $margenPromedio = round($sumaMaxenes / $totalItems, 1);
}

// Calcular estadísticas por grupo
function calcularEstadisticasPorGrupo($costos) {
    $estadisticasGrupos = [];
    
    foreach ($costos as $item) {
        $grupo = $item['grupo'] ?? 'Sin Grupo';
        
        if (!isset($estadisticasGrupos[$grupo])) {
            $estadisticasGrupos[$grupo] = [
                'cantidad' => 0,
                'costoTotal' => 0,
                'fijos' => 0,
                'variables' => 0,
                'activos' => 0
            ];
        }
        
        $estadisticasGrupos[$grupo]['cantidad']++;
        $estadisticasGrupos[$grupo]['costoTotal'] += $item['costoUSD'];
        
        if ($item['tipo_costo'] === 'Fijo') {
            $estadisticasGrupos[$grupo]['fijos']++;
        } else {
            $estadisticasGrupos[$grupo]['variables']++;
        }
        
        if ($item['activo'] ?? true) {
            $estadisticasGrupos[$grupo]['activos']++;
        }
    }
    
    // Calcular promedios
    foreach ($estadisticasGrupos as $grupo => &$stats) {
        $stats['costoPromedio'] = $stats['cantidad'] > 0 ? $stats['costoTotal'] / $stats['cantidad'] : 0;
    }
    
    return $estadisticasGrupos;
}

$estadisticasGrupos = calcularEstadisticasPorGrupo($costos);

// Estadísticas adicionales
function calcularEstadisticasDetalladas($costos) {
    $fijos = array_filter($costos, function($item) { return $item['tipo_costo'] === 'Fijo'; });
    $variables = array_filter($costos, function($item) { return $item['tipo_costo'] === 'Variable'; });
    
    $costoTotal = array_sum(array_column($costos, 'costoUSD'));
    $costoPromedio = count($costos) > 0 ? $costoTotal / count($costos) : 0;
    
    return [
        'totalFijos' => count($fijos),
        'totalVariables' => count($variables),
        'costoTotal' => $costoTotal,
        'costoPromedio' => $costoPromedio,
        'categorias' => count(array_unique(array_column($costos, 'categoria'))),
        'grupos' => count(array_unique(array_column($costos, 'grupo')))
    ];
}

$estadisticasDetalladas = calcularEstadisticasDetalladas($costos);
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
    <!-- Header simplificado -->
    <header class="header">
        <div class="header-content">
            <h1>
                <span>⚙️</span>
                Administración
            </h1>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="mostrarEstadisticasDetalladas()" title="Ver estadísticas completas">
                    <span>📊</span> Estadísticas
                </button>
                <button class="btn btn-secondary" onclick="mostrarGestionGrupos()" title="Gestionar grupos">
                    <span>🏷️</span> Grupos
                </button>
                <button class="btn btn-secondary" onclick="exportarItems()" title="Exportar items">
                    <span>📤</span> Exportar
                </button>
                <a href="index.php" class="btn btn-primary">← Cotizador</a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Mostrar mensajes de éxito/error -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" id="success-alert">
                <span>✅</span>
                <div>
                    <?php
                    switch ($_GET['success']) {
                        case 'item_saved':
                            echo 'Item guardado correctamente';
                            break;
                        case 'item_deleted':
                            echo 'Item eliminado correctamente';
                            break;
                        case 'bulk_updated':
                            $count = $_GET['count'] ?? 0;
                            echo "Se actualizaron {$count} items correctamente";
                            break;
                        default:
                            echo 'Operación completada exitosamente';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Estadísticas principales -->
        <div class="stats-container">
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-value"><?php echo $totalItems; ?></span>
                    <div class="stat-label">Total Items</div>
                </div>
                
                <div class="stat-card">
                    <span class="stat-value"><?php echo count($grupos); ?></span>
                    <div class="stat-label">Grupos</div>
                </div>
                
                <div class="stat-card">
                    <span class="stat-value"><?php echo $margenPromedio; ?>%</span>
                    <div class="stat-label">Margen Promedio</div>
                </div>
            </div>
            
            <button class="stats-toggle" onclick="mostrarEstadisticasDetalladas()">
                Ver más estadísticas →
            </button>
        </div>

        <!-- Pestañas -->
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('items')">Items</button>
            <button class="tab" onclick="cambiarTab('grupos')">Por Grupos</button>
            <button class="tab" onclick="cambiarTab('margenes')">Márgenes</button>
        </div>

        <!-- Filtros -->
        <div class="filters-container">
            <div class="filters-grid">
                <div class="search-container">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="search-items" class="search-input" placeholder="Buscar items...">
                </div>
                
                <select id="filter-tipo" class="form-control">
                    <option value="">Tipo</option>
                    <option value="Fijo">Fijo</option>
                    <option value="Variable">Variable</option>
                </select>
                
                <select id="filter-categoria" class="form-control">
                    <option value="">Categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select id="filter-grupo" class="form-control">
                    <option value="">Grupo</option>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo htmlspecialchars($grupo); ?>"><?php echo htmlspecialchars($grupo); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button class="btn btn-secondary" onclick="limpiarFiltros()">Limpiar</button>
            </div>
        </div>

        <!-- Tab: Items -->
        <div id="tab-items" class="tab-content active">
            <div class="admin-table">
                <div class="table-header">
                    <h3>Gestión de Items</h3>
                    <div class="table-actions">
                        <button class="btn btn-secondary" onclick="toggleSeleccionMasiva()" id="btn-seleccion">
                            <span>☑️</span> Selección Múltiple
                        </button>
                        <button class="btn btn-primary" onclick="openModal('item-modal')">
                            <span>➕</span> Nuevo Item
                        </button>
                    </div>
                </div>
                
                <!-- Acciones masivas -->
                <div id="bulk-actions" class="bulk-actions" style="display: none;">
                    <div style="padding: 1rem; background: var(--surface-hover); border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 1rem;">
                        <span id="selected-count">0 items seleccionados</span>
                        
                        <select id="bulk-grupo" style="padding: 0.5rem;">
                            <option value="">Cambiar grupo...</option>
                            <?php foreach ($grupos as $grupo): ?>
                                <option value="<?php echo htmlspecialchars($grupo); ?>"><?php echo htmlspecialchars($grupo); ?></option>
                            <?php endforeach; ?>
                            <option value="nuevo">+ Nuevo Grupo</option>
                        </select>
                        
                        <button class="btn btn-primary" onclick="aplicarCambiosMasivos()">Aplicar</button>
                        <button class="btn btn-secondary" onclick="cancelarSeleccionMasiva()">Cancelar</button>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all" onchange="toggleSelectAll()" style="display: none;">
                            </th>
                            <th>Tipo</th>
                            <th>Grupo</th>
                            <th>Categoría</th>
                            <th>Producto</th>
                            <th>Item</th>
                            <th>Costo USD</th>
                            <th>Margen</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="items-table">
                        <?php foreach ($costos as $index => $item): ?>
                            <?php
                            $itemId = $item['id'] ?? $index;
                            $margen = $item['margen_custom'] ?? $margenGlobal;
                            $precio = $item['costoUSD'] / (1 - $margen / 100);
                            $activo = $item['activo'] ?? true;
                            $grupo = $item['grupo'] ?? 'Sin Grupo';
                            ?>
                            <tr class="item-row" data-id="<?php echo $itemId; ?>" 
                                data-tipo="<?php echo $item['tipo_costo']; ?>"
                                data-categoria="<?php echo htmlspecialchars($item['categoria']); ?>"
                                data-grupo="<?php echo htmlspecialchars($grupo); ?>"
                                style="<?php echo !$activo ? 'opacity: 0.6;' : ''; ?>">
                                
                                <td>
                                    <input type="checkbox" class="item-checkbox" value="<?php echo $itemId; ?>" style="display: none;">
                                </td>
                                <td>
                                    <span class="tag tag-<?php echo strtolower($item['tipo_costo']); ?>">
                                        <?php echo $item['tipo_costo']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="tag" style="background: rgba(37, 99, 235, 0.1); color: var(--primary);">
                                        <?php echo htmlspecialchars($grupo); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($item['categoria']); ?></td>
                                <td><?php echo htmlspecialchars($item['tipo_prod']); ?></td>
                                <td>
                                    <div class="text-truncate" title="<?php echo htmlspecialchars($item['item']); ?>">
                                        <?php echo htmlspecialchars($item['item']); ?>
                                    </div>
                                </td>
                                <td class="font-mono">$<?php echo number_format($item['costoUSD'], 2); ?></td>
                                <td>
                                    <span class="status-indicator <?php echo $item['margen_custom'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $margen; ?>%
                                    </span>
                                </td>
                                <td>
                                    <span class="status-indicator <?php echo $activo ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $activo ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td class="table-actions-cell">
                                    <button class="btn btn-secondary" onclick="editarItem(<?php echo $itemId; ?>)">
                                        <span>✏️</span>
                                    </button>
                                    <button class="btn btn-danger" onclick="eliminarItem(<?php echo $itemId; ?>)">
                                        <span>🗑️</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Por Grupos -->
        <div id="tab-grupos" class="tab-content">
            <div class="admin-table">
                <div class="table-header">
                    <h3>Vista por Grupos</h3>
                    <div class="table-actions">
                        <button class="btn btn-secondary" onclick="expandirTodosGrupos()">
                            <span>📂</span> Expandir Todo
                        </button>
                        <button class="btn btn-secondary" onclick="contraerTodosGrupos()">
                            <span>📁</span> Contraer Todo
                        </button>
                    </div>
                </div>
                
                <div class="grupos-container">
                    <?php foreach ($estadisticasGrupos as $grupo => $stats): ?>
                        <div class="grupo-section" data-grupo="<?php echo htmlspecialchars($grupo); ?>">
                            <div class="grupo-header" onclick="toggleGrupo('<?php echo htmlspecialchars($grupo); ?>')">
                                <div class="grupo-info">
                                    <h4>
                                        <span class="toggle-icon">▼</span>
                                        <?php echo htmlspecialchars($grupo); ?>
                                        <span class="grupo-badge"><?php echo $stats['cantidad']; ?> items</span>
                                    </h4>
                                    <div class="grupo-stats">
                                        <span>💰 $<?php echo number_format($stats['costoTotal'], 0); ?></span>
                                        <span>📊 $<?php echo number_format($stats['costoPromedio'], 2); ?> promedio</span>
                                        <span>🔧 <?php echo $stats['fijos']; ?> fijos</span>
                                        <span>⚡ <?php echo $stats['variables']; ?> variables</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grupo-items" style="display: block;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Categoría</th>
                                            <th>Item</th>
                                            <th>Costo USD</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $itemsGrupo = array_filter($costos, function($item) use ($grupo) {
                                            return ($item['grupo'] ?? 'Sin Grupo') === $grupo;
                                        });
                                        ?>
                                        <?php foreach ($itemsGrupo as $item): ?>
                                            <?php $itemId = $item['id'] ?? 0; ?>
                                            <tr>
                                                <td>
                                                    <span class="tag tag-<?php echo strtolower($item['tipo_costo']); ?>">
                                                        <?php echo $item['tipo_costo']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['categoria']); ?></td>
                                                <td>
                                                    <div class="text-truncate" title="<?php echo htmlspecialchars($item['item']); ?>">
                                                        <?php echo htmlspecialchars($item['item']); ?>
                                                    </div>
                                                </td>
                                                <td class="font-mono">$<?php echo number_format($item['costoUSD'], 2); ?></td>
                                                <td>
                                                    <span class="status-indicator <?php echo ($item['activo'] ?? true) ? 'status-active' : 'status-inactive'; ?>">
                                                        <?php echo ($item['activo'] ?? true) ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <td class="table-actions-cell">
                                                    <button class="btn btn-secondary" onclick="editarItem(<?php echo $itemId; ?>)">✏️</button>
                                                    <button class="btn btn-danger" onclick="eliminarItem(<?php echo $itemId; ?>)">🗑️</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Márgenes -->
        <div id="tab-margenes" class="tab-content">
            <div class="admin-table">
                <div class="table-header">
                    <h3>Gestión de Márgenes</h3>
                    <div class="table-actions">
                        <button class="btn btn-primary" onclick="guardarMargenes()">
                            <span>💾</span> Guardar Cambios
                        </button>
                    </div>
                </div>
                
                <div style="padding: 1.5rem;">
                    <div class="form-group" style="max-width: 300px; margin-bottom: 2rem;">
                        <label for="margen-global">Margen Global (%)</label>
                        <input type="number" id="margen-global" value="<?php echo $margenGlobal; ?>" min="0" max="99" step="1">
                        <button class="btn btn-secondary" onclick="aplicarMargenGlobal()" style="margin-top: 0.5rem;">
                            Aplicar a todos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear/editar item -->
    <div id="item-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="item-modal-title">Nuevo Item</h3>
                <button onclick="closeModal('item-modal')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
            </div>
            
            <form id="item-form" method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="save_item">
                    <input type="hidden" id="edit-item-id" name="item_id" value="">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tipo_costo">Tipo de Costo *</label>
                            <select id="tipo_costo" name="tipo_costo" required>
                                <option value="">Seleccionar...</option>
                                <option value="Fijo">Fijo</option>
                                <option value="Variable">Variable</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="recurrencia">Recurrencia *</label>
                            <select id="recurrencia" name="recurrencia" required>
                                <option value="">Seleccionar...</option>
                                <option value="Mensual">Mensual</option>
                                <option value="Anual">Anual</option>
                                <option value="Única">Única</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="grupo">Grupo *</label>
                            <select id="grupo" name="grupo" required onchange="manejarCambioGrupo()">
                                <option value="">Seleccionar grupo...</option>
                                <?php foreach ($grupos as $grupo): ?>
                                    <option value="<?php echo htmlspecialchars($grupo); ?>"><?php echo htmlspecialchars($grupo); ?></option>
                                <?php endforeach; ?>
                                <option value="nuevo">+ Crear Nuevo Grupo</option>
                            </select>
                            
                            <!-- Campo para nuevo grupo (oculto por defecto) -->
                            <input type="text" id="nuevo-grupo" placeholder="Nombre del nuevo grupo..." 
                                   style="display: none; margin-top: 0.5rem;" onblur="confirmarNuevoGrupo()">
                        </div>
                        
                        <div class="form-group">
                            <label for="categoria">Categoría *</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                                <?php endforeach; ?>
                                <option value="nueva">+ Nueva Categoría</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo_prod">Tipo de Producto *</label>
                            <input type="text" id="tipo_prod" name="tipo_prod" placeholder="Ej: Whatsapp, Email, SMS..." required 
                                   list="tipos-producto">
                            <datalist id="tipos-producto">
                                <?php foreach ($tiposProducto as $tipo): ?>
                                    <option value="<?php echo htmlspecialchars($tipo); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="item">Nombre del Item *</label>
                            <input type="text" id="item" name="item" placeholder="Descripción detallada del item..." required>
                        </div>
                        
                        <div class="form-group">
                            <label for="costoUSD">Costo USD *</label>
                            <input type="number" id="costoUSD" name="costoUSD" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="margen_custom">Margen Personalizado (%)</label>
                            <input type="number" id="margen_custom" name="margen_custom" min="0" max="99" step="1" placeholder="Usar margen global">
                        </div>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="notas">Notas (opcional)</label>
                            <textarea id="notas" name="notas" rows="3" placeholder="Notas adicionales sobre el item..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('item-modal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de estadísticas detalladas -->
    <div id="stats-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Estadísticas Detalladas</h3>
                <button onclick="closeModal('stats-modal')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
            </div>
            
            <div class="modal-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-value"><?php echo $estadisticasDetalladas['totalFijos']; ?></span>
                        <div class="stat-label">Items Fijos</div>
                    </div>
                    
                    <div class="stat-card">
                        <span class="stat-value"><?php echo $estadisticasDetalladas['totalVariables']; ?></span>
                        <div class="stat-label">Items Variables</div>
                    </div>
                    
                    <div class="stat-card">
                        <span class="stat-value">$<?php echo number_format($estadisticasDetalladas['costoTotal'], 0); ?></span>
                        <div class="stat-label">Costo Total</div>
                    </div>
                    
                    <div class="stat-card">
                        <span class="stat-value">$<?php echo number_format($estadisticasDetalladas['costoPromedio'], 2); ?></span>
                        <div class="stat-label">Costo Promedio</div>
                    </div>
                    
                    <div class="stat-card">
                        <span class="stat-value"><?php echo $estadisticasDetalladas['categorias']; ?></span>
                        <div class="stat-label">Categorías</div>
                    </div>
                    
                    <div class="stat-card">
                        <span class="stat-value"><?php echo $estadisticasDetalladas['grupos']; ?></span>
                        <div class="stat-label">Grupos</div>
                    </div>
                </div>
                
                <!-- Estadísticas por grupo -->
                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Estadísticas por Grupo</h4>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($estadisticasGrupos as $grupo => $stats): ?>
                            <div style="padding: 1rem; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 0.5rem;">
                                <div style="font-weight: 600; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($grupo); ?></div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.5rem; font-size: 0.875rem; color: var(--text-secondary);">
                                    <div>📊 <?php echo $stats['cantidad']; ?> items</div>
                                    <div>💰 $<?php echo number_format($stats['costoTotal'], 0); ?></div>
                                    <div>📈 $<?php echo number_format($stats['costoPromedio'], 2); ?> avg</div>
                                    <div>✅ <?php echo $stats['activos']; ?> activos</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('stats-modal')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal de gestión de grupos -->
    <div id="grupos-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Gestionar Grupos</h3>
                <button onclick="closeModal('grupos-modal')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
            </div>
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="nuevo-grupo-nombre">Crear Nuevo Grupo</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="nuevo-grupo-nombre" placeholder="Nombre del grupo..." style="flex: 1;">
                        <button class="btn btn-primary" onclick="crearNuevoGrupo()">Crear</button>
                    </div>
                </div>
                
                <div style="margin-top: 2rem;">
                    <h4>Grupos Existentes</h4>
                    <div id="lista-grupos">
                        <?php foreach ($estadisticasGrupos as $grupo => $stats): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 0.5rem;">
                                <div>
                                    <strong><?php echo htmlspecialchars($grupo); ?></strong>
                                    <span style="color: var(--text-secondary); margin-left: 0.5rem;">(<?php echo $stats['cantidad']; ?> items)</span>
                                </div>
                                <button class="btn btn-danger" onclick="eliminarGrupo('<?php echo htmlspecialchars($grupo); ?>')" 
                                        <?php echo $stats['cantidad'] > 0 ? 'disabled title="No se puede eliminar un grupo con items"' : ''; ?>>
                                    🗑️
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('grupos-modal')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Formularios ocultos -->
    <form id="bulk-form" method="POST" action="" style="display: none;">
        <input type="hidden" name="action" value="bulk_update_grupo">
        <input type="hidden" id="bulk-selected-items" name="selected_items" value="">
        <input type="hidden" id="bulk-nuevo-grupo" name="nuevo_grupo" value="">
    </form>

    <!-- Incluir admin.js (SIN JavaScript inline para evitar duplicaciones) -->
    <script src="admin.js"></script>
    
    <!-- Solo estilos adicionales CSS -->
    <style>
        /* Estilos adicionales para los grupos */
        .grupos-container {
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .grupo-section {
            margin-bottom: 1.5rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .grupo-header {
            background: var(--background);
            padding: 1rem;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            transition: background-color 0.2s;
        }
        
        .grupo-header:hover {
            background: var(--surface-hover);
        }
        
        .grupo-info h4 {
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .toggle-icon {
            font-size: 0.8rem;
            transition: transform 0.2s;
        }
        
        .grupo-badge {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .grupo-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .grupo-items {
            background: var(--surface);
        }
        
        .grupo-items table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .grupo-items th,
        .grupo-items td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }
        
        .grupo-items th {
            background: var(--background);
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .bulk-actions {
            background: var(--surface-hover);
            border-bottom: 1px solid var(--border);
        }
        
        /* Responsive para grupos */
        @media (max-width: 768px) {
            .grupo-stats {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .grupo-items th,
            .grupo-items td {
                padding: 0.5rem 0.25rem;
                font-size: 0.8rem;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
        }
    </style>
</body>
</html>