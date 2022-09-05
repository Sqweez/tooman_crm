<?php

namespace App\Actions\Revision;

use App\Http\Controllers\Services\ExcelService;
use App\Http\Resources\v2\Revision\RevisionProductResource;
use App\Revision;
use App\RevisionProducts;
use App\v2\Models\ProductSku;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateRevisionPivotTableAction {

    /**
     * @throws Exception
     */
    public function handle(Revision $revision): string {
        $products = $this->loadRevisionProducts($revision);
        $path = $this->generatePivotTable($products);
        $revision->update([
            'original_pivot_file' => $path,
            'status' => Revision::STATUS_ON_APPROVE,
            'checking_user_id' => auth()->id(),
        ]);
        return $path;
    }

    private function loadRevisionProducts(Revision $revision): array {
        $with = array_map(function ($item) {
            return 'sku.' . $item;
        }, ProductSku::PRODUCT_SKU_WITH_CART_LIST);

        $revisionProducts = RevisionProducts::query()
            ->whereRevisionId($revision->id)
            ->with($with)
            ->get();

        return collect(RevisionProductResource::collection($revisionProducts))
            ->sortBy('category_id')
            ->toArray();
    }

    /**
     * @throws Exception
     */
    private function generatePivotTable(array $products): string {
        $excelTemplate = ExcelService::loadExcelTemplate(Revision::PIVOT_FILE_TEMPLATE);
        $sheet = $excelTemplate->getActiveSheet();
        $products = collect($products)->map(function ($product) {
            return [
                'id' => $product['product_id'],
                'name' => $product['full_product_name'],
                'category' => $product['category'],
                'product_price' => number_format($product['product_price'], 2, ',', ','),
                'stock_quantity' => $product['stock_quantity'],
                'fact_quantity' => $product['fact_quantity'],
                'delta' => $product['delta'],
                'edit' => '',
            ];
        })->toArray();

        $sheet->fromArray($products, null, 'A3', true);
        foreach ($products as $key => $product) {
            if ($product['delta'] < 0) {
                $cellIndex = $key + 3;
                $sheet
                    ->getStyle("G$cellIndex")
                    ->getFont()
                    ->getColor()
                    ->setRGB('FF0000');
            }
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ],
            'font' => [
                'name' => 'Arial',
                'size' => 8
            ]
        ];

        $sheet->getStyle('A2:H' . (count($products) + 2))
            ->applyFromArray($styleArray);

        foreach (range('A', 'H') as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        return ExcelService::saveExcelFile($excelTemplate, Revision::PIVOT_FILE_NAME, 'revisions');
    }
}
