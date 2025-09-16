<?php
// exportar.php - Versión sin errores de PhpSpreadsheet
error_reporting(E_ERROR | E_WARNING | E_PARSE); // Ocultar notices

if (!isset($_POST['data']) || empty($_POST['data'])) {
    die('Error: No se recibieron datos para exportar.');
}

// Intentar PhpSpreadsheet pero con fallback inmediato si hay problemas
$useExcel = false;
$autoloadPaths = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php'
];

foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        try {
            require_once $path;
            if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                $useExcel = true;
            }
        } catch (Exception $e) {
            // Si hay cualquier error, usar CSV
            $useExcel = false;
        }
        break;
    }
}

// Decodificar datos una sola vez
$rawData = json_decode($_POST['data'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error: Datos JSON inválidos - ' . json_last_error_msg());
}

// Extraer información común
$cliente = $rawData['cliente'] ?? 'Cliente no especificado';
$proyecto = $rawData['proyecto'] ?? 'Proyecto no especificado';
$margen = $rawData['margen'] ?? '50';
$fecha = $rawData['fecha'] ?? date('d/m/Y');
$hora = $rawData['hora'] ?? date('H:i:s');
$items = $rawData['items'] ?? $rawData;

// Decidir formato según capacidades
if ($useExcel) {
    try {
        crearExcelSeguro();
    } catch (Exception $e) {
        // Si falla Excel, usar CSV
        crearCSV();
    }
} else {
    crearCSV();
}

function crearExcelSeguro() {
    global $cliente, $proyecto, $margen, $fecha, $hora, $items;
    
    // Importar clases solo cuando las necesitemos
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;
    use PhpOffice\PhpSpreadsheet\Style\Border;
    use PhpOffice\PhpSpreadsheet\Style\Fill;
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Configurar propiedades básicas
    $spreadsheet->getProperties()
        ->setCreator('SkyTel Cotizador')
        ->setTitle('Cotización - ' . $cliente);
    
    // ENCABEZADO SIMPLE
    $sheet->setCellValue('A1', 'COTIZACIÓN SKYTEL');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    
    // INFORMACIÓN BÁSICA
    $sheet->setCellValue('A3', 'Cliente: ' . $cliente);
    $sheet->setCellValue('A4', 'Proyecto: ' . $proyecto);
    $sheet->setCellValue('A5', 'Fecha: ' . $fecha);
    $sheet->setCellValue('A6', 'Margen: ' . $margen . '%');
    
    // ENCABEZADOS DE TABLA (fila 8)
    $headers = ['Tipo', 'Categoría', 'Item', 'Costo USD', 'Cantidad', 'Subtotal', 'Precio Venta'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '8', $header);
        $sheet->getStyle($col . '8')->getFont()->setBold(true);
        $col++;
    }
    
    // DATOS - Convertir todo a string para evitar errores de tipo
    $row = 9;
    $total = 0;
    
    foreach ($items as $item) {
        $tipo = (string)($item['tipo_costo'] ?? $item['tipo'] ?? '');
        $categoria = (string)($item['categoria'] ?? '');
        $itemDesc = (string)($item['item'] ?? '');
        $costoUSD = (string)number_format(floatval($item['costoUSD'] ?? 0), 4);
        $cantidad = (string)intval($item['cantidad'] ?? 0);
        $subtotal = (string)number_format(floatval($item['subtotal'] ?? 0), 2);
        $precioVenta = (string)number_format(floatval($item['precioVenta'] ?? 0), 2);
        
        // Insertar como strings para evitar errores de tipo
        $sheet->setCellValueExplicit('A' . $row, $tipo, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B' . $row, $categoria, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C' . $row, $itemDesc, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D' . $row, '$' . $costoUSD, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('E' . $row, $cantidad, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . $row, '$' . $subtotal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('G' . $row, '$' . $precioVenta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        
        $total += floatval($item['precioVenta'] ?? 0);
        $row++;
    }
    
    // TOTAL
    $sheet->setCellValue('F' . $row, 'TOTAL:');
    $sheet->setCellValue('G' . $row, '$' . number_format($total, 2));
    $sheet->getStyle('F' . $row . ':G' . $row)->getFont()->setBold(true);
    
    // Auto-ajustar columnas
    foreach (range('A', 'G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $writer = new Xlsx($spreadsheet);
    $clienteSlug = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente);
    $filename = "Cotizacion_{$clienteSlug}_" . date('Y-m-d_H-i') . ".xlsx";
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
}

function crearCSV() {
    global $cliente, $proyecto, $margen, $fecha, $hora, $items;
    
    $clienteSlug = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente);
    $filename = "Cotizacion_{$clienteSlug}_" . date('Y-m-d_H-i') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Información básica
    fputcsv($output, ['COTIZACIÓN SKYTEL']);
    fputcsv($output, ['']);
    fputcsv($output, ['Cliente:', $cliente]);
    fputcsv($output, ['Proyecto:', $proyecto]);
    fputcsv($output, ['Fecha:', $fecha]);
    fputcsv($output, ['Hora:', $hora]);
    fputcsv($output, ['Margen:', $margen . '%']);
    fputcsv($output, ['']);
    
    // Encabezados
    fputcsv($output, [
        'Tipo Costo', 'Recurrencia', 'Categoría', 'Tipo Producto', 
        'Descripción', 'Costo USD', 'Cantidad', 'Subtotal', 'Precio Venta'
    ]);
    
    // Datos
    $total = 0;
    foreach ($items as $item) {
        $row = [
            $item['tipo_costo'] ?? $item['tipo'] ?? '',
            $item['recurrencia'] ?? '',
            $item['categoria'] ?? '',
            $item['tipo_prod'] ?? $item['tipoProd'] ?? '',
            $item['item'] ?? '',
            '$' . number_format(floatval($item['costoUSD'] ?? 0), 4),
            intval($item['cantidad'] ?? 0),
            '$' . number_format(floatval($item['subtotal'] ?? 0), 2),
            '$' . number_format(floatval($item['precioVenta'] ?? 0), 2)
        ];
        
        fputcsv($output, $row);
        $total += floatval($item['precioVenta'] ?? 0);
    }
    
    // Total
    fputcsv($output, ['']);
    fputcsv($output, ['', '', '', '', '', '', 'TOTAL:', '', '$' . number_format($total, 2)]);
    
    // Resumen
    fputcsv($output, ['']);
    fputcsv($output, ['RESUMEN']);
    fputcsv($output, ['Items cotizados:', count($items)]);
    fputcsv($output, ['Total cotización:', '$' . number_format($total, 2)]);
    
    fclose($output);
}
?>