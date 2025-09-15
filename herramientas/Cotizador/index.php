<?php
// Leer archivo JSON
$data = json_decode(file_get_contents('costos.json'), true);
$costos = $data['costos'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Cotizador Minimalista</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>

  <h1 ondblclick="document.getElementById('adminLink').style.display = 'inline-block';">
    Cotizador Minimalista
  </h1>

  <div class="container-right">
    <a href="admin.php" id="adminLink" class="btn-admin" style="display:none;">
      Panel Admin
    </a>
  </div>

  <div class="container-right" style="margin-bottom: 10px;">
    <form method="post" action="exportar.php" id="exportForm" style="display: inline-block; margin-right: 20px;">
      <input type="hidden" name="data" id="exportData">
      <button type="submit">Exportar presupuesto a Excel</button>
    </form>
    <label for="margen">Margen %:</label>
    <input type="number" id="margen" value="50" min="0" max="99" step="1" />
  </div>

  <table>
    <thead>
      <tr>
        <th>Tipo Costo</th>
        <th>Recurrencia</th>
        <th>Categoria</th>
        <th>Tipo Prod</th>
        <th>Item</th>
        <th>Costo USD</th>
        <th>Cantidad</th>
        <th>Subtotal</th>
        <th>Precio Venta</th>
      </tr>
      <tr class="filter-row">
        <th><input type="text" class="filter-input" data-col="0" placeholder="Filtrar..." /></th>
        <th><input type="text" class="filter-input" data-col="1" placeholder="Filtrar..." /></th>
        <th><input type="text" class="filter-input" data-col="2" placeholder="Filtrar..." /></th>
        <th><input type="text" class="filter-input" data-col="3" placeholder="Filtrar..." /></th>
        <th><input type="text" class="filter-input" data-col="4" placeholder="Filtrar..." /></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($costos as $c): ?>
      <tr class="main-row" data-costo="<?= floatval($c['costoUSD']) ?>">
        <td data-label="Tipo Costo"><?= htmlspecialchars($c['tipo_costo']) ?></td>
        <td data-label="Recurrencia"><?= htmlspecialchars($c['recurrencia']) ?></td>
        <td data-label="Categoria"><?= htmlspecialchars($c['categoria']) ?></td>
        <td data-label="Tipo Prod"><?= htmlspecialchars($c['tipo_prod']) ?></td>
        <td data-label="Item" title="<?= htmlspecialchars($c['item']) ?>"><?= htmlspecialchars($c['item']) ?></td>
        <td data-label="Costo USD" style="text-align:right;"><?= number_format(floatval($c['costoUSD']), 2) ?></td>
        <td data-label="Cantidad" style="text-align:right;">
          <input type="number" class="cantidad" min="0" value="0" />
        </td>
        <td data-label="Subtotal" class="subtotal" style="text-align:right;">0.00</td>
        <td data-label="Precio Venta" class="precio-venta" style="text-align:right;">0.00</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div id="resultado">Total: $0.00</div>

  <script>
    const margenInput = document.getElementById('margen');

    function calcular() {
      const margen = parseFloat(margenInput.value) / 100;
      let total = 0;
      let exportRows = [];

      document.querySelectorAll('tr.main-row').forEach(row => {
        const costo = parseFloat(row.getAttribute('data-costo'));
        const inputCantidad = row.querySelector('input.cantidad');
        const cantidad = parseFloat(inputCantidad.value) || 0;
        const tipo = row.children[0].textContent;
        const recurrencia = row.children[1].textContent;
        const categoria = row.children[2].textContent;
        const tipoProd = row.children[3].textContent;
        const item = row.children[4].textContent;

        const subtotal = costo * cantidad;
        const precioVenta = subtotal / (1 - margen);

        row.querySelector('.subtotal').textContent = subtotal.toFixed(2);
        row.querySelector('.precio-venta').textContent = precioVenta.toFixed(2);

        total += precioVenta;

        if (cantidad > 0) {
          exportRows.push({
            tipo,
            recurrencia,
            categoria,
            tipoProd,
            item,
            costoUSD: costo,
            cantidad,
            subtotal,
            precioVenta
          });
        }
      });

      resultado.textContent = 'Total: $' + total.toFixed(2);
      document.getElementById('exportData').value = JSON.stringify(exportRows);
    }

    margenInput.addEventListener('input', calcular);
    document.querySelectorAll('input.cantidad').forEach(input => {
      input.addEventListener('input', calcular);
    });

    calcular();

    // FILTROS EN LA TABLA
    const filters = document.querySelectorAll('.filter-input');
    filters.forEach(filter => {
      filter.addEventListener('input', () => {
        const colIndex = parseInt(filter.getAttribute('data-col'));
        const filterValue = filter.value.toLowerCase();

        document.querySelectorAll('tbody tr.main-row').forEach(row => {
          const cell = row.children[colIndex];
          if (!cell) return;
          const text = cell.textContent.toLowerCase();
          if (text.indexOf(filterValue) === -1) {
            row.style.display = 'none';
          } else {
            row.style.display = '';
          }
        });
      });
    });
  </script>

</body>
</html>
