<?php
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$data = json_decode($_POST['data'], true);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Escribir encabezados
$sheet->fromArray(
    ['Tipo', 'Recurrencia', 'Categoría', 'Tipo Prod', 'Item', 'Costo USD', 'Cantidad', 'Subtotal', 'Precio Venta'],
    null,
    'A1'
);

// Escribir los datos
$rowIndex = 2;
$total = 0;
foreach ($data as $item) {
    $sheet->fromArray([
        $item['tipo'],
        $item['recurrencia'],
        $item['categoria'],
        $item['tipoProd'],
        $item['item'],
        $item['costoUSD'],
        $item['cantidad'],
        $item['subtotal'],
        $item['precioVenta']
    ], null, 'A' . $rowIndex);

    $total += $item['precioVenta'];
    $rowIndex++;
}

// Escribir total en la última fila
$sheet->setCellValue('H' . $rowIndex, 'TOTAL:');
$sheet->setCellValue('I' . $rowIndex, $total);

// Opcional: poner en negrita la fila de total
$sheet->getStyle('H' . $rowIndex . ':I' . $rowIndex)->getFont()->setBold(true);

// Descargar el archivo
$writer = new Xlsx($spreadsheet);
$filename = 'presupuesto.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
