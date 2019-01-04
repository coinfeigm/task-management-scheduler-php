<?php
/** Include PHPExcel */
set_time_limit(0);
ini_set('memory_limit', '-1');
require_once '../vendors/classes/PHPExcel.php';

ini_set('max_execution_time', 1000);

//Excel Data 
$templateFileName = '../vendors/template/training.xlsx';
$reader = PHPExcel_IOFactory::createReaderForFile($templateFileName);
$templateSpreadsheet = $reader->load($templateFileName);
$spreadsheet = $templateSpreadsheet;
$sheet = $spreadsheet->getActiveSheet();

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"【Polaris】WBS.xlsx\"");
header("Cache-Control: max-age=0");

$trainingData = json_decode(file_get_contents("php://input"), true);

if (!empty($trainingData)) {
    if (count($trainingData) - 1 > 0){
        $sheet->insertNewRowBefore(7, count($trainingData) - 1); 
    }
    $row = 6;
    foreach ($trainingData as $value) {
        $sheet->setCellValue('B' . $row, $value['PKG']);
        $sheet->setCellValue('C' . $row, $value['HOSPITALNO']);
        $sheet->setCellValue('D' . $row, ($value['HOSPITALNAME'] != '') ? $value['HOSPITALNAME'] : ' ');
        $sheet->setCellValue('E' . $row, ($value['CTRLNO'] != '') ? $value['CTRLNO'] : ' ');
        $sheet->setCellValue('F' . $row, $value['FORMNAME']);
        $sheet->setCellValue('G' . $row, ($value['OLDID'] != '') ? $value['OLDID'] : ' ');
        $sheet->setCellValue('H' . $row, ($value['NEWID'] != '') ? $value['NEWID'] : ' ');
        $sheet->setCellValue('I' . $row, ($value['TEAM'] != '') ? $value['TEAM'] : ' ');
        
        $sheet->setCellValue('J' . $row, $value['KANANAME']);
        if ((intval($value['STATUSFLG']) == 1) || (intval($value['ELAPSEDDAYS']) < 0)) {
            cellColor('J' . $row, '0099FF');
        } elseif ((intval($value['STATUSFLG']) == 2) || (intval($value['STATUSFLG']) == 3)) {
            cellColor('B' . $row . ':P' . $row, 'BFBFBF');
        }
        if ((intval($value['MEMBERSTATUS']) == 1) || (intval($value['MEMBERSTATUS']) == 2) || (intval($value['MEMBERSTATUS']) == 3)) {
            cellColor('J' . $row, '000000');
            fontColor('J' . $row, 'FFFFFF');
        }
        $sheet->setCellValue('K' . $row, $value['NAME']);
        $sheet->setCellValue('L' . $row, ($value['CHATNAME'] != '') ? $value['CHATNAME'] : ' ');
        $sheet->setCellValue('M' . $row, ($value['STARTDATE'] != '') ? $value['STARTDATE'] : ' ');
        $sheet->setCellValue('N' . $row, ($value['ENDDATE'] != '') ? $value['ENDDATE'] : ' ');
        $sheet->setCellValue('O' . $row, $value['TASKNO'] . " ");
        $sheet->setCellValue('P' . $row, ($value['DURATION'] != '') ? $value['DURATION'] : ' ');
        $sheet->setCellValue('Q' . $row, ($value['REMARKS'] != '') ? $value['REMARKS'] : ' ');
        $row = $row + 1;
    }
}

$writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel2007');
ob_start();
$writer->save("php://output");
$xlsData = ob_get_contents();
ob_end_clean();

$response =  array(
        'op' => 'ok',
        'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
    );

die(json_encode($response));
  
function cellColor($cells,$color){
    global $sheet;

    $sheet->getStyle($cells)->getFill()->applyFromArray(array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array(
             'rgb' => $color
        )
    ));
}

function fontColor($cells,$color){
    global $sheet;

    $sheet->getStyle($cells)->applyFromArray(array(
        'font' => array(
            'color' => array('rgb' => $color
        )
    )));
}

?>