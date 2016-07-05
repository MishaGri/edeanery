<?php
require_once("PHPExcel/Classes/PHPExcel.php");
function parse ($filename)
{
    $excelReader = PHPExcel_IOFactory::createReaderForFile($filename);
    $excelObj = $excelReader->load($filename);
    $worksheet = $excelObj->getActiveSheet();
    $lastrow = $worksheet->getHighestRow();
    var_dump($lastrow);
    for ($row = 1; $row <= $lastrow; $row++) {
        echo $worksheet->getCell('A' . $row)->getValue();
        echo "   ";
        echo $worksheet->getCell('B' . $row)->getValue();
        echo "<br>";
    }
}

