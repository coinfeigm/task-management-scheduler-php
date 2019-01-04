<?php
/** Include PHPExcel */
set_time_limit(0);
ini_set('memory_limit', '-1');
require_once '../vendors/classes/PHPExcel.php';

ini_set('max_execution_time', 1000);

$json = file_get_contents("php://input");

$data = json_decode($json, true);
$startDate = $_GET['start'];
$endDate = $_GET['end'];

if ($startDate != '' && $endDate != '') {
    if (!empty($data)) {
            
        //Excel Data 
        $templateFileName = '../vendors/template/polaris.xlsx';
        $reader = PHPExcel_IOFactory::createReaderForFile($templateFileName);
        $templateSpreadsheet = $reader->load($templateFileName);
        $spreadsheet = $templateSpreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        //Header
        $start = str_replace("-", "/", $startDate);
        $end = str_replace("-", "/", $endDate);
        $sheet->setCellValue('A2', '出力期間: ' . $start . '～' . $end);

        //Column Insert
        $date1 = strtotime($startDate);
        $date2 = strtotime($endDate);
        $days = $date2 - $date1;
        $interval = $days / (60 * 60 * 24);
        if ($interval > 0) {
            $sheet->insertNewColumnBefore('M', $interval); 
        }

        //Set Column Header Dates
        $col = 11;
        $dateCount = $interval;
        for ($x = 0; $x <= $dateCount; $x++) {
            $colIdx = PHPExcel_Cell::stringFromColumnIndex($col);
            $dateHdr = date('y/m/d', strtotime($startDate. ' + ' . $x . ' days'));
            $sheet->setCellValue($colIdx . '4', $dateHdr);
            $col = $col  + 1;
        }

        //Row insert
        if (count($data) - 1 > 0){
            $sheet->insertNewRowBefore(6, count($data) - 1); 
        }

        //Set Row Data
        $row = 5;
        foreach ($data as $value) {
            $col = 11;
            $sheet->setCellValue('B' . $row, $value['package']);
            $sheet->setCellValue('C' . $row, $value['hospitalno']);
            $sheet->setCellValue('D' . $row, ($value['hospitalname'] != '') ? $value['hospitalname'] : ' ');
            $sheet->setCellValue('E' . $row, ($value['controlno'] != '') ? $value['controlno'] : ' ');
            $sheet->setCellValue('F' . $row, $value['taskname']);
            $sheet->setCellValue('G' . $row, $value['taskno'] . ' ');
            $sheet->setCellValue('H' . $row, $value['kananame']);
            $sheet->setCellValue('I' . $row, $value['name']);
            $sheet->setCellValue('J' . $row, date('y/m/d', strtotime($value['startdate'])));
            $sheet->setCellValue('K' . $row, date('y/m/d', strtotime($value['enddate'])));

            for ($x = 0; $x <= $dateCount; $x++) {
                $colIdx = PHPExcel_Cell::stringFromColumnIndex($col);

                $sheet->setCellValue($colIdx . $row, $value['day' . ($x + 1)]);
 
                $col = $col  + 1;
            }

            //Row Total
            $colIdx = PHPExcel_Cell::stringFromColumnIndex($col);
            $colss = PHPExcel_Cell::stringFromColumnIndex($col - 1);
            $sheet->setCellValue($colIdx . $row, "=COUNTIF(L". $row . ":" . $colss . $row . ",\"■\")");

            $row = $row + 1;
        }

        //Column Total
        $row = count($data) + 5;
        $col = 11;
        for ($x = 0; $x <= $dateCount; $x++) {
            $colIdx = PHPExcel_Cell::stringFromColumnIndex($col);
            $sheet->setCellValue($colIdx . $row, "=COUNTIF(" . $colIdx . "5:" . $colIdx . ($row - 1) . ",\"■\")");
            $col = $col  + 1;
        }

        //Overall Total
        $colIdx = PHPExcel_Cell::stringFromColumnIndex($col);
        $colss = PHPExcel_Cell::stringFromColumnIndex($col - 1);
        $sheet->setCellValue($colIdx . $row,"=SUM(L". $row . ":" . $colss . $row . ")");

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"【Polaris】WBS.xlsx\"");
        header("Cache-Control: max-age=0");

        $writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel2007');
        $writer->setPreCalculateFormulas(true);
        ob_start();
        $writer->save("php://output");
        $xlsData = ob_get_contents();
        ob_end_clean();

        $response =  array(
            'op' => 'ok',
            'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
        );

        die(json_encode($response));
    } else {
        echo "No records found";
    }
} else {
    echo "Empty Start date or End date";
}

?>