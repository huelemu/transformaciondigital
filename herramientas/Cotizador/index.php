<?php
// Leer archivo JSON con los costos
$data = json_decode(file_get_contents('costos.json'), true);
$costos = $data['costos'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador Profesional - SkyTel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 ondblclick="document.getElementById('adminLink').style.display = 'inline-block';">
                💼 Cotizador Profesional
            </h1>
            <div class="header-actions">
                <button type="button" class="btn btn-success" onclick="exportarCotizacion()">
                    <span>📊</span> Exportar Excel
                </button>
                <a href="admin.php" id="adminLink" class="btn btn-admin">
                    <span>⚙️</span> Admin
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Sección de Controles -->
        <div class="controls-section">
            <div class="controls-grid">
                <div class="control-group">
                    <label for="margen">Margen de Ganancia</label>
                    <div class="input-with-icon">
                        <span class="input-icon">%</span>
                        <input type="number" id="margen" class="with-icon" value="50" min="0" max="99" step="1">
                    </div>
                </div>
                <div class="control-group">
                    <label for="cliente">Cliente</label>
                    <input type="text" id="cliente" placeholder="Nombre del cliente...">
                </div>
                <div class="control-group">
                    <label for="proyecto">Proyecto</label>
                    <input type="text" id="proyecto" placeholder="Nombre del proyecto...">
                </div>
                <div class="control-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-secondary" onclick="limpiarCotizacion()">
                        <span>🗑️</span> Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Sección de la Tabla -->
        <div class="table-container">
            <div class="table-header">
                <h3>Items de Cotización</h3>
                <div class="filters-grid">
                    <input type="text" class="filter-input" data-col="0" placeholder="Filtrar por tipo...">
                    <input type="text" class="filter-input" data-col="1" placeholder="Filtrar por recurrencia...">
                    <input type="text" class="filter-input" data-col="2" placeholder="Filtrar por categoría...">
                    <input type="text" class="filter-input" data-col="3" placeholder="Filtrar por producto...">
                    <input type="text" class="filter-input" data-col="4" placeholder="Filtrar por item...">
                </div>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Recurrencia</th>
                            <th>Categoría</th>
                            <th>Producto</th>
                            <th>Item</th>
                            <th>Costo USD</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Precio Venta</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-costos">
                        <?php foreach ($costos as $index => $item): ?>
                        <tr class="main-row">
                            <td>
                                <span class="tag tag-<?= strtolower($item['tipo_costo']) ?>">
                                    <?= htmlspecialchars($item['tipo_costo']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="tag tag-<?= strtolower($item['recurrencia']) ?>">
                                    <?= htmlspecialchars($item['recurrencia']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($item['categoria']) ?></td>
                            <td><?= htmlspecialchars($item['tipo_prod']) ?></td>
                            <td class="tooltip" data-tooltip="<?= htmlspecialchars($item['item']) ?>">
                                <?= strlen($item['item']) > 50 ? substr(htmlspecialchars($item['item']), 0, 50) . '...' : htmlspecialchars($item['item']) ?>
                            </td>
                            <td class="money">$<?= number_format($item['costoUSD'], 4) ?></td>
                            <td>
                                <input type="number" 
                                       class="cantidad-input cantidad" 
                                       min="0" 
                                       step="1" 
                                       value="0" 
                                       data-index="<?= $index ?>"
                                       data-costo="<?= $item['costoUSD'] ?>"
                                       data-tipo="<?= htmlspecialchars($item['tipo_costo']) ?>"
                                       data-recurrencia="<?= htmlspecialchars($item['recurrencia']) ?>"
                                       data-categoria="<?= htmlspecialchars($item['categoria']) ?>"
                                       data-producto="<?= htmlspecialchars($item['tipo_prod']) ?>"
                                       data-item="<?= htmlspecialchars($item['item']) ?>">
                            </td>
                            <td class="money subtotal">$0.00</td>
                            <td class="money precio-venta positive">$0.00</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sección de Resumen -->
        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Items Seleccionados</div>
                    <div class="summary-value" id="items-count">0</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Costo Total</div>
                    <div class="summary-value money" id="costo-total">$0.00</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Margen Aplicado</div>
                    <div class="summary-value" id="margen-display">50%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Cotización</div>
                    <div class="summary-value total money positive" id="total-cotizacion">$0.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario oculto para exportación -->
    <form method="post" action="exportar.php" id="exportForm" style="display: none;">
        <input type="hidden" name="data" id="exportData">
    </form>

    <script src="cotizador.js"></script>
</body>
</html>