<?php
// exportar.php - Exportar cotizaciones a Excel con todos los campos
// SkyTel Cotizador - Versión corregida completa

ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ob_start();

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
    ob_end_clean();
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
            $useExcel = false;
        }
        break;
    }
}

// ===============================
// DECODIFICAR DATOS JSON
// ===============================
$rawData = json_decode($_POST['data'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    ob_end_clean();
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
$items    = $rawData['items']    ?? [];

// ===============================
// DECISIÓN DE FORMATO
// ===============================
if ($useExcel) {
    try {
        crearExcelProfesional();
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
 * Crear Excel con diseño profesional
 */
function crearExcelProfesional() {
    global $cliente, $proyecto, $margen, $fecha, $hora, $items;
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Propiedades del archivo
    $spreadsheet->getProperties()
        ->setCreator('SkyTel Cotizador')
        ->setTitle('Cotización - ' . $cliente)
        ->setSubject('Cotización de servicios');
    
    // ENCABEZADO PRINCIPAL
    $sheet->setCellValue('A1', 'COTIZACIÓN SKYTEL');
    $sheet->mergeCells('A1:I1');
    $headerStyle = [
        'font' => [
            'bold' => true,
            'size' => 18,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '2563EB']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ];
    $sheet->getStyle('A1')->applyFromArray($headerStyle);
    $sheet->getRowDimension('1')->setRowHeight(35);
    
    // INFORMACIÓN GENERAL
    $infoData = [
        ['Cliente:', $cliente],
        ['Proyecto:', $proyecto],
        ['Fecha:', $fecha],
        ['Hora:', $hora],
        ['Margen Global:', $margen . '%']
    ];
    
    $startRow = 3;
    foreach ($infoData as $index => $data) {
        $row = $startRow + $index;
        $sheet->setCellValue('A' . $row, $data[0]);
        $sheet->setCellValue('B' . $row, $data[1]);
    }
    
    // Estilo para etiquetas
    $labelStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => '374151']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
    ];
    $sheet->getStyle('A3:A7')->applyFromArray($labelStyle);
    
    // ENCABEZADOS DE TABLA
    $headers = [
        'Tipo', 'Categoría', 'Grupo', 'Item', 'Costo USD', 
        'Margen %', 'Cantidad', 'Subtotal', 'Precio Venta'
    ];
    
    $headerRow = 9;
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headerRow, $header);
        $col++;
    }
    
    // Estilo para encabezados de tabla
    $tableHeaderStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A9:I9')->applyFromArray($tableHeaderStyle);
    $sheet->getRowDimension('9')->setRowHeight(25);
    
    // DATOS DE LA TABLA
    $row = 10;
    foreach ($items as $item) {
        $tipo        = (string)($item['tipo_costo'] ?? '');
        $categoria   = (string)($item['categoria'] ?? '');
        $grupo       = (string)($item['grupo'] ?? 'Sin Grupo');
        $itemDesc    = (string)($item['item'] ?? '');
        $costoUSD    = floatval($item['costoUSD'] ?? 0);
        $margenPers  = $item['margenPersonalizado'] ?? null;
        $margenUsar  = $margenPers !== null ? $margenPers : floatval($margen);
        $cantidad    = intval($item['cantidad'] ?? 0);
        
        // Insertar datos
        $sheet->setCellValue('A' . $row, $tipo);
        $sheet->setCellValue('B' . $row, $categoria);
        $sheet->setCellValue('C' . $row, $grupo);
        $sheet->setCellValue('D' . $row, $itemDesc);
        $sheet->setCellValue('E' . $row, $costoUSD);
        $sheet->setCellValue('F' . $row, $margenUsar);
        $sheet->setCellValue('G' . $row, $cantidad);
        
        // FÓRMULAS DE EXCEL
        $sheet->setCellValue('H' . $row, "=E{$row}*G{$row}");
        $sheet->setCellValue('I' . $row, "=H{$row}/(1-F{$row}/100)");
        
        $row++;
    }
    
    // Estilo para filas de datos
    $lastDataRow = $row - 1;
    
    if ($lastDataRow >= 10) {
        $dataStyle = [
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]]
        ];
        $sheet->getStyle('A10:I' . $lastDataRow)->applyFromArray($dataStyle);
        
        // Zebra striping
        for ($i = 10; $i <= $lastDataRow; $i++) {
            if (($i - 10) % 2 == 1) {
                $sheet->getStyle('A' . $i . ':I' . $i)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F9FAFB');
            }
        }
        
        // FORMATEO DE NÚMEROS
        $sheet->getStyle('E10:E' . $lastDataRow)->getNumberFormat()->setFormatCode('$#,##0.0000');
        $sheet->getStyle('F10:F' . $lastDataRow)->getNumberFormat()->setFormatCode('0"%"');
        $sheet->getStyle('H10:I' . $lastDataRow)->getNumberFormat()->setFormatCode('$#,##0.00');
        
        // FILA DE TOTAL
        $totalRow = $row;
        $sheet->setCellValue('G' . $totalRow, 'TOTAL:');
        $sheet->mergeCells('G' . $totalRow . ':H' . $totalRow);
        $sheet->setCellValue('I' . $totalRow, "=SUM(I10:I{$lastDataRow})");
        
        // Estilo para total
        $totalStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THICK]]
        ];
        $sheet->getStyle('G' . $totalRow . ':I' . $totalRow)->applyFromArray($totalStyle);
        $sheet->getStyle('I' . $totalRow)->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getRowDimension($totalRow)->setRowHeight(30);
    }
    
    // AJUSTES FINALES
    $columnWidths = [
        'A' => 12, 'B' => 15, 'C' => 15, 'D' => 35, 'E' => 12,
        'F' => 10, 'G' => 10, 'H' => 12, 'I' => 15
    ];
    
    foreach ($columnWidths as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }
    
    // Configurar página
    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
    
    // GENERAR Y DESCARGAR ARCHIVO
    $clienteSlug = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cliente);
    $filename = "Cotizacion_{$clienteSlug}_" . date('Y-m-d_H-i') . ".xlsx";
    
    // Limpiar buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Headers HTTP
    if (!headers_sent()) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

/**
 * Crear archivo CSV (fallback)
 */
function crearCSV() {
    global $cliente, $proyecto, $margen, $fecha, $hora, $items;
    
    $clienteSlug = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cliente);
    $filename = "Cotizacion_{$clienteSlug}_" . date('Y-m-d_H-i') . ".csv";
    
    // Limpiar buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    if (!headers_sent()) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');
    }
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezado
    fputcsv($output, ['COTIZACIÓN SKYTEL']);
    fputcsv($output, ['']);
    fputcsv($output, ['Cliente:', $cliente]);
    fputcsv($output, ['Proyecto:', $proyecto]);
    fputcsv($output, ['Fecha:', $fecha]);
    fputcsv($output, ['Margen Global:', $margen . '%']);
    fputcsv($output, ['']);
    
    // Headers de tabla
    fputcsv($output, [
        'Tipo', 'Categoría', 'Grupo', 'Item', 'Costo USD', 
        'Margen %', 'Cantidad', 'Subtotal', 'Precio Venta'
    ]);
    
    // Datos
    $total = 0;
    foreach ($items as $item) {
        $margenPers = $item['margenPersonalizado'] ?? null;
        $margenUsar = $margenPers !== null ? $margenPers : floatval($margen);
        
        $row = [
            $item['tipo_costo'] ?? '',
            $item['categoria'] ?? '',
            $item['grupo'] ?? 'Sin Grupo',
            $item['item'] ?? '',
            '$' . number_format(floatval($item['costoUSD'] ?? 0), 4),
            $margenUsar . '%',
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
    
    fclose($output);
    exit;
}
?>