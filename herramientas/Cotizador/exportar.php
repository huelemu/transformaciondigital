<?php
// exportar.php - Exportar cotizaciones a Excel (PhpSpreadsheet) o CSV (fallback)
// SkyTel Cotizador

// Mostrar solo errores importantes
error_reporting(E_ERROR | E_WARNING | E_PARSE); 

// ===============================
// IMPORTS de PhpSpreadsheet
// ===============================
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// ===============================
// VALIDACIÓN DE DATOS DE ENTRADA
// ===============================
if (!isset($_POST['data']) || empty($_POST['data'])) {
    die('Error: No se recibieron datos para exportar.');
}

// ===============================
// AUTOLOAD DE COMPOSER
// ===============================
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
            $useExcel = false; // Si falla, forzar CSV
        }
        break;
    }
}

// ===============================
// DECODIFICAR DATOS JSON
// ===============================
$rawData = json_decode($_POST['data'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error: Datos JSON inválidos - ' . json_last_error_msg());
}

// ===============================
// VARIABLES COMUNES
// ===============================
$cliente  = $rawData['cliente']  ?? 'Cliente no especificado';
$proyecto = $rawData['proyecto'] ?? 'Proyecto no especificado';
$margen   = $rawData['margen']   ?? '50';
$fecha    = $rawData['fecha']    ?? date('d/m/Y');
$hora     = $rawData['hora']     ?? date('H:i:s');
$items    = $rawData['items']    ?? $rawData;

// ===============================
// DECISIÓN DE FORMATO
// ===============================
if ($useExcel) {
    try {
        crearExcelSeguro();
    } catch (Exception $e) {
        crearCSV(); // Fallback si falla PhpSpreadsheet
    }
} else {
    crearCSV();
}

// ===============================
// FUNCIONES
// ===============================

/**
 * Crear Excel con PhpSpreadsheet
 */
function crearExcelSeguro() {
    global $cliente, $proyecto, $margen, $fecha, $hora, $items;
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Propiedades del archivo
    $spreadsheet->getProperties()
        ->setCreator('SkyTel Cotizador')
        ->setTitle('Cotización - ' . $cliente);
    
    // ENCABEZADO PRINCIPAL
    $sheet->setCellValue('A1', 'COTIZACIÓN SKYTEL');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    
    // INFORMACIÓN GENERAL
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
    
    // DATOS
    $row = 9;
    $total = 0;
    
    foreach ($items as $item) {
        $tipo        = (string)($item['tipo_costo'] ?? $item['tipo'] ?? '');
        $categoria   = (string)($item['categoria'] ?? '');
        $itemDesc    = (string)($item['item'] ?? '');
        $costoUSD    = (string)number_format(floatval($item['costoUSD'] ?? 0), 4);
        $cantidad    = (string)intval($item['cantidad'] ?? 0);
        $subtotal    = (string)number_format(floatval($item['subtotal'] ?? 0), 2);
        $precioVenta = (string)number_format(floatval($item['precioVenta'] ?? 0), 2);
        
        // Insertar como texto para evitar problemas de formato
        $sheet->setCellValueExplicit('A' . $row, $tipo,        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B' . $row, $categoria,   \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C' . $row, $itemDesc,    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D' . $row, '$' . $costoUSD, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('E' . $row, $cantidad,    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . $row, '$' . $subtotal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('G' . $row, '$' . $precioVenta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        
        $total += floatval($item['precioVenta'] ?? 0);
        $row++;
    }
    
    // TOTAL
    $sheet->setCellValue('F' . $row, 'TOTAL:');
    $sheet->setCellValue('G' . $row, '$' . number_format($total, 2));
    $sheet->getStyle('F' . $row . ':G' . $row)->getFont()->setBold(true);
    
    // Ajustar tamaño de columnas
    foreach (range('A', 'G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Nombre de archivo seguro
    $clienteSlug = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente);
    $filename = "Cotizacion_{$clienteSlug}_" . date('Y-m-d_H-i') . ".xlsx";
    
    // Enviar encabezados y archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

/**
 * Crear archivo CSV (fallback si no hay PhpSpreadsheet)
 */
function crearCSV() {
    global $cliente, $proyecto, $margen, $fecha, $hora, $items;
    
    $clienteSlug = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente);
    $filename = "Cotizacion_{$clienteSlug}_" . date('Y-m-d_H-i') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // Agregar BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezado de documento
    fputcsv($output, ['COTIZACIÓN SKYTEL']);
    fputcsv($output, ['']);
    fputcsv($output, ['Cliente:', $cliente]);
    fputcsv($output, ['Proyecto:', $proyecto]);
    fputcsv($output, ['Fecha:', $fecha]);
    fputcsv($output, ['Hora:', $hora]);
    fputcsv($output, ['Margen:', $margen . '%']);
    fputcsv($output, ['']);
    
    // Encabezados de tabla
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
