<?php
// exportar.php - Versión corregida sin errores de sintaxis
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DECLARACIONES USE AL PRINCIPIO
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Verificar que se enviaron datos
if (!isset($_POST['data']) || empty($_POST['data'])) {
    die('Error: No se recibieron datos para exportar.');
}

// Intentar cargar PhpSpreadsheet
$autoloadPaths = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php'
];

$autoloadFound = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require $path;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    // Si no hay PhpSpreadsheet, crear CSV como alternativa
    crearCSV();
    exit;
}

try {
    // Verificar que PhpSpreadsheet esté disponible
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        throw new Exception('PhpSpreadsheet no está disponible');
    }
    
    crearExcel();
    
} catch (Exception $e) {
    // Si falla Excel, crear CSV como respaldo
    error_log('Error al crear Excel: ' . $e->getMessage());
    crearCSV();
}

function crearExcel() {
    // Decodificar datos
    $rawData = json_decode($_POST['data'], true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }

    // Extraer información
    $cliente = $rawData['cliente'] ?? 'Cliente no especificado';
    $proyecto = $rawData['proyecto'] ?? 'Proyecto no especificado';
    $margen = $rawData['margen'] ?? '50';
    $fecha = $rawData['fecha'] ?? date('d/m/Y');
    $hora = $rawData['hora'] ?? date('H:i:s');
    $items = $rawData['items'] ?? $rawData; // Compatibilidad con formato anterior

    // Crear nuevo spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Cotización');

    // Configurar información del documento
    $spreadsheet->getProperties()
        ->setCreator('SkyTel Cotizador')
        ->setTitle('Cotización - ' . $cliente)
        ->setSubject('Cotización generada automáticamente')
        ->setDescription('Cotización creada el ' . $fecha . ' a las ' . $hora);

    // ENCABEZADO PRINCIPAL
    $sheet->setCellValue('A1', 'COTIZACIÓN SKYTEL');
    $sheet->mergeCells('A1:I1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // INFORMACIÓN DEL CLIENTE
    $currentRow = 3;
    $sheet->setCellValue('A' . $currentRow, 'INFORMACIÓN GENERAL');
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(12);
    
    $currentRow++;
    $sheet->setCellValue('A' . $currentRow, 'Cliente:');
    $sheet->setCellValue('B' . $currentRow, $cliente);
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
    
    $currentRow++;
    $sheet->setCellValue('A' . $currentRow, 'Proyecto:');
    $sheet->setCellValue('B' . $currentRow, $proyecto);
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
    
    $currentRow++;
    $sheet->setCellValue('A' . $currentRow, 'Fecha:');
    $sheet->setCellValue('B' . $currentRow, $fecha);
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
    
    $currentRow++;
    $sheet->setCellValue('A' . $currentRow, 'Margen aplicado:');
    $sheet->setCellValue('B' . $currentRow, $margen . '%');
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);

    // ENCABEZADOS DE LA TABLA
    $currentRow += 2;
    $headers = [
        'Tipo Costo', 'Recurrencia', 'Categoría', 'Tipo Producto', 
        'Descripción', 'Costo USD', 'Cantidad', 'Subtotal', 'Precio Venta'
    ];
    
    $headerRow = $currentRow;
    foreach ($headers as $index => $header) {
        $column = chr(65 + $index); // A, B, C, etc.
        $sheet->setCellValue($column . $headerRow, $header);
    }
    
    // Estilo para encabezados
    $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '2563EB']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);

    // DATOS DE LA TABLA
    $currentRow++;
    $total = 0;
    $totalCosto = 0;
    
    foreach ($items as $item) {
        // Asegurar que todos los campos existen
        $tipo = $item['tipo_costo'] ?? $item['tipo'] ?? '';
        $recurrencia = $item['recurrencia'] ?? '';
        $categoria = $item['categoria'] ?? '';
        $tipoProd = $item['tipo_prod'] ?? $item['tipoProd'] ?? '';
        $descripcion = $item['item'] ?? '';
        $costoUSD = floatval($item['costoUSD'] ?? 0);
        $cantidad = intval($item['cantidad'] ?? 0);
        $subtotal = floatval($item['subtotal'] ?? 0);
        $precioVenta = floatval($item['precioVenta'] ?? 0);

        $sheet->fromArray([
            $tipo,
            $recurrencia,
            $categoria,
            $tipoProd,
            $descripcion,
            $costoUSD,
            $cantidad,
            $subtotal,
            $precioVenta
        ], null, 'A' . $currentRow);

        // Formatear números
        $sheet->getStyle('F' . $currentRow)->getNumberFormat()->setFormatCode('$#,##0.0000');
        $sheet->getStyle('H' . $currentRow)->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle('I' . $currentRow)->getNumberFormat()->setFormatCode('$#,##0.00');

        $total += $precioVenta;
        $totalCosto += $subtotal;
        $currentRow++;
    }

    // FILA DE TOTALES
    $currentRow++;
    $sheet->setCellValue('G' . $currentRow, 'TOTALES:');
    $sheet->setCellValue('H' . $currentRow, $totalCosto);
    $sheet->setCellValue('I' . $currentRow, $total);
    
    // Estilo para totales
    $sheet->getStyle('G' . $currentRow . ':I' . $currentRow)->applyFromArray([
        'font' => ['bold' => true, 'size' => 12],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'F3F4F6']
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THICK]
        ]
    ]);
    
    $sheet->getStyle('H' . $currentRow . ':I' . $currentRow)->getNumberFormat()->setFormatCode('$#,##0.00');

    // RESUMEN ADICIONAL
    $currentRow += 2;
    $sheet->setCellValue('A' . $currentRow, 'RESUMEN');
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(12);
    
    $currentRow++;
    $sheet->setCellValue('A' . $currentRow, 'Items cotizados:');
    $sheet->setCellValue('B' . $currentRow, count($items));
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
    
    $currentRow++;
    $sheet->setCellValue('A' . $currentRow, 'Costo total:');
    $sheet->setCellValue('B' . $currentRow, '$' . number_format($totalCosto, 2));
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
    
    $currentRow++;
    $sheet->setCellValue('A' . $currentRow, 'Precio de venta:');
    $sheet->setCellValue('B' . $currentRow, '$' . number_format($total, 2));
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);

    // AJUSTAR ANCHO DE COLUMNAS
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Aplicar bordes a toda la tabla de datos
    $sheet->getStyle('A' . $headerRow . ':I' . ($currentRow - 6))->applyFromArray([
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);

    // GENERAR ARCHIVO
    $writer = new Xlsx($spreadsheet);
    
    // Crear nombre de archivo único
    $clienteSlug = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente);
    $fechaSlug = date('Y-m-d_H-i');
    $filename = "Cotizacion_{$clienteSlug}_{$fechaSlug}.xlsx";

    // Headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    $writer->save('php://output');
}

function crearCSV() {
    // Función de respaldo para crear CSV si Excel falla
    $rawData = json_decode($_POST['data'], true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error: Datos JSON inválidos');
    }

    // Extraer información
    $cliente = $rawData['cliente'] ?? 'Cliente no especificado';
    $proyecto = $rawData['proyecto'] ?? 'Proyecto no especificado';
    $margen = $rawData['margen'] ?? '50';
    $fecha = $rawData['fecha'] ?? date('d/m/Y');
    $items = $rawData['items'] ?? $rawData;

    // Crear nombre de archivo
    $clienteSlug = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente);
    $fechaSlug = date('Y-m-d_H-i');
    $filename = "Cotizacion_{$clienteSlug}_{$fechaSlug}.csv";

    // Headers para descarga CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Crear archivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Información del cliente
    fputcsv($output, ['COTIZACIÓN SKYTEL'], ';');
    fputcsv($output, [''], ';');
    fputcsv($output, ['Cliente', $cliente], ';');
    fputcsv($output, ['Proyecto', $proyecto], ';');
    fputcsv($output, ['Fecha', $fecha], ';');
    fputcsv($output, ['Margen', $margen . '%'], ';');
    fputcsv($output, [''], ';');

    // Encabezados
    fputcsv($output, [
        'Tipo Costo', 'Recurrencia', 'Categoría', 'Tipo Producto', 
        'Descripción', 'Costo USD', 'Cantidad', 'Subtotal', 'Precio Venta'
    ], ';');

    // Datos
    $total = 0;
    foreach ($items as $item) {
        $tipo = $item['tipo_costo'] ?? $item['tipo'] ?? '';
        $recurrencia = $item['recurrencia'] ?? '';
        $categoria = $item['categoria'] ?? '';
        $tipoProd = $item['tipo_prod'] ?? $item['tipoProd'] ?? '';
        $descripcion = $item['item'] ?? '';
        $costoUSD = floatval($item['costoUSD'] ?? 0);
        $cantidad = intval($item['cantidad'] ?? 0);
        $subtotal = floatval($item['subtotal'] ?? 0);
        $precioVenta = floatval($item['precioVenta'] ?? 0);

        fputcsv($output, [
            $tipo, $recurrencia, $categoria, $tipoProd, $descripcion,
            number_format($costoUSD, 4), $cantidad, 
            number_format($subtotal, 2), number_format($precioVenta, 2)
        ], ';');

        $total += $precioVenta;
    }

    // Total
    fputcsv($output, [''], ';');
    fputcsv($output, ['', '', '', '', '', '', 'TOTAL:', '', number_format($total, 2)], ';');

    fclose($output);
}
?>