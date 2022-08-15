<?php


namespace App\Http\Controllers\Services;


use App\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelService {

    public function createExcel($fileName = 'products.json') {
        $fileName = 'products.json';
        $jsonFile = Storage::get('public/json/' . $fileName);
        $data = json_decode($jsonFile, true);
        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'Название');
        $sheet->setCellValue('C1', 'Категория');
        $sheet->setCellValue('D1', 'Атрибуты');
        $sheet->setCellValue('E1', 'Производитель');
        $sheet->setCellValue('F1', 'Стоимость');
        $sheet->setCellValue('G1', 'Количество');


        foreach ($data as $key => $datum) {
            $sheet->setCellValue('A' . ($key + 2), $datum['id']);
            $sheet->setCellValue('B' . ($key + 2), $datum['product_name']);
            $sheet->setCellValue('C' . ($key + 2), $datum['categories']);
            $sheet->setCellValue('D' . ($key + 2), 'Атрибуты');
            $sheet->setCellValue('E' . ($key + 2), $datum['manufacturer']);
            $sheet->setCellValue('F' . ($key + 2), $datum['product_price']);
            $sheet->setCellValue('G' . ($key + 2), $datum['quantity']);

            $attributes = '';

            foreach ($datum['attributes'] as $attribute) {
                $attributes .= $attribute['attribute'] . ":" . $attribute['attribute_value'] . ' | ';
            }
            $sheet->setCellValue('D' . ($key + 2), $attributes);
        }

        $writer = new Xlsx($spreadSheet);

        $writer->save('products.xlsx');

        return $data;
    }

    public function createRevisionExcel($jsonData = []) {
        $spreadSheet = new Spreadsheet();

        $sheet = $spreadSheet->getActiveSheet();

        $spreadSheet->getDefaultStyle()->getFont()->setName('Arial');

        $sheet->setCellValue('A1', 'Артикул');
        $sheet->setCellValue('B1', 'Наименование');
        $sheet->setCellValue('C1', 'Категория');
        $sheet->setCellValue('D1', 'Атрибуты');
        $sheet->setCellValue('E1', 'Производитель');
        $sheet->setCellValue('F1', 'Стоимость');
        $sheet->setCellValue('G1', 'Количество');
        $sheet->setCellValue('H1', 'Факт количество');

        $key = 0;

        foreach ($jsonData as $key => $datum) {
            $sheet->setCellValue('A' . ($key + 2), $datum['id']);
            $sheet->setCellValue('B' . ($key + 2), $datum['product_name']);
            $sheet->setCellValue('C' . ($key + 2), $datum['categories']);
            $sheet->setCellValue('D' . ($key + 2), $datum['attributes']);
            $sheet->setCellValue('E' . ($key + 2), $datum['manufacturer']);
            $sheet->setCellValue('F' . ($key + 2), $datum['product_price']);
            $sheet->setCellValue('G' . ($key + 2), $datum['quantity']);
        }

        foreach (range("B", "H") as $coordinate) {
            $sheet->getStyle('D2:D' . $key)->getAlignment()->setWrapText(true);

            if ($coordinate !== "D") {
                $sheet->getColumnDimension($coordinate)->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadSheet);
        $fileName =  Carbon::today()->toDateString() . "_ревизия_" .Str::random(10) . '.xlsx';
        $path = 'storage/excel/revisions/';
        \File::ensureDirectoryExists($path);
        $fullPath = $path . $fileName;
        $writer->save($fullPath);
        return url('/') . Storage::url($fullPath);
    }

    public function parseRevisionExcel($filename) {
        $file = explode('/', $filename);
        $file = $file[1] . '/' . $file[2];
        $excelFile = $this->loadFile($file, '');
        $sheet = $excelFile->getActiveSheet();
        $rows = $sheet->getRowIterator();
        $products = [];
        foreach ($rows as $key => $row) {
            if ($key > 1) {
                $products[] = [
                    'id' => $sheet->getCell('A' . $key)->getValue(),
                    'stock_quantity' => $sheet->getCell('G' . $key)->getValue(),
                    'fact_quantity' => $sheet->getCell('H' . $key)->getValue() ?? 0,
                ];
            }
        }

        return $products;
    }

    public function parseExcel($filename) {
        $excelFile = $this->loadFile($filename);
        $sheet = $excelFile->getActiveSheet();
        $rows = $sheet->getRowIterator();
        $products = [];
        foreach ($rows as $key => $item) {
            if ($key > 1) {
                $count = intval($sheet->getCell('G' . $key)->getValue());
                if ($count > 0) {
                    array_push($products, ['id' => $sheet->getCell('A' . $key)->getValue(), 'name' => $sheet->getCell('B' . $key)->getValue(), 'count' => $sheet->getCell('G' . $key)->getValue(),]);
                }

            }
        }
        $jsonData = json_encode($products, JSON_UNESCAPED_UNICODE);
        $fileName = 'public/json/' . $filename . '.json';
        Storage::put($fileName, $jsonData);
        return $products;
    }

    public function loadFile($filename, $ext = 'xlsx') {
        $path = 'app/public/excel/' . $filename . '.' . $ext;
        $file = storage_path($path);
        return IOFactory::load($file);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createRevisionFile(array $_products): string {
        $excelTemplate = $this->loadFile('revision_template');
        $excelSheet = $excelTemplate->getActiveSheet();
        $INITIAL_ROW = 3;
        $products = array_map(function ($product) {
            return [
                'id' => $product['product_id'],
                'name' => sprintf("%s %s", $product['product_name'], $product['attributes']),
                'category' => $product['category'],
                'product_price' => $product['product_price'],
                'fact_quantity' => 0
            ];
        }, $_products);

        $excelSheet->fromArray($products, null, 'A3');

        /*foreach ($revision['products'] as $key => $product) {
            $currentIndex = $key + $INITIAL_ROW;
            try {
                $excelSheet->insertNewRowBefore($currentIndex, 1);
            } catch (Exception $e) {
            }
            $excelSheet->setCellValue('A' . $currentIndex, $product['product_id']);
            $excelSheet->setCellValue('B' . $currentIndex, sprintf("%s %s", $product['product_name'], $product['attributes']));
            $excelSheet->setCellValue('C' . $currentIndex, $product['category']);
            $excelSheet->setCellValue('D' . $currentIndex, $product['product_price']);
            $excelSheet->setCellValue('E' . $currentIndex, 0);
        }*/

        $excelWriter = new Xlsx($excelTemplate);
        $fileName =  'РЕВИЗИЯ' . "_" . Carbon::today()->toDateString() . "_" . Str::random(10) . '.xlsx';
        $path = 'storage/excel/revisions/';
        \File::ensureDirectoryExists($path);
        $fullPath =  $path . $fileName;
        $excelWriter->save($fullPath);
        return $fullPath;
    }
}
