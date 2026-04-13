<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$spreadsheet->getActiveSheet()->setCellValue('A1', 'Sheet1Data');
$spreadsheet->createSheet()->setCellValue('A1', 'Sheet2Data');
$writer = new Xlsx($spreadsheet);
$writer->save('test.xlsx');

class TestImport implements ToArray {
    public $calls = 0;
    public $data = [];
    public function array(array $array) {
        $this->calls++;
        $this->data[] = $array[0][0] ?? null;
    }
}

$import = new TestImport();
Excel::import($import, 'test.xlsx');

echo "Calls: " . $import->calls . "\n";
print_r($import->data);
