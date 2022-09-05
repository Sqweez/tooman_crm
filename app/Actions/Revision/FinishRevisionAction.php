<?php

namespace App\Actions\Revision;

use App\Http\Controllers\Services\ExcelService;
use App\Http\Resources\v2\Revision\RevisionProductResource;
use App\Revision;
use App\RevisionProducts;
use App\Services\Revision\RevisionService;
use App\v2\Models\ProductSku;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

class FinishRevisionAction {

    /**
     * @throws Exception
     */
    public function handle(Revision $revision): string {
        $products = $this->loadRevisionProducts($revision);
        $path = $this->generateResultPivotTable($products);
        $revision->update([
            'status' => Revision::STATUS_FINISHED,
            'finished_at' => now(),
            'is_finished' => true,
            'final_pivot_file' => $path
        ]);
        return $path;
    }

    private function loadRevisionProducts(Revision $revision): array {
        $revisionProducts = RevisionService::loadRevisionProductWithNested($revision, false);

        return collect(RevisionProductResource::collection($revisionProducts))
            ->sortBy('category_id')
            ->toArray();
    }

    /**
     * @throws Exception
     */
    private function generateResultPivotTable(array $products): string {
        $excelTemplate = ExcelService::loadExcelTemplate(Revision::RESULT_PIVOT_FILE_TEMPLATE);
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
                'stock_product_price_sum' => number_format($product['stock_product_price_sum'], 2, ',', ','),
                'fact_product_price_sum' => number_format($product['fact_product_price_sum'], 2, ',', ','),
                'product_price_sum_delta' => number_format($product['product_price_sum_delta'], 2, ',', ','),
            ];
        })->toArray();
        $sheet->fromArray($products, null, 'A3', true);
        foreach ($products as $key => $product) {
            $cellIndex = $key + 3;
            if ($product['delta'] < 0) {
                $sheet
                    ->getStyle("G$cellIndex")
                    ->getFont()
                    ->getColor()
                    ->setRGB('FF0000');
            }

            if ($product['product_price_sum_delta'] < 0) {
                $sheet
                    ->getStyle("J$cellIndex")
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

        $sheet->getStyle('A2:J' . (count($products) + 2))
            ->applyFromArray($styleArray);

        foreach (range('A', 'J') as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        return ExcelService::saveExcelFile($excelTemplate, Revision::RESULT_PIVOT_FILE_NAME, 'revisions');
    }
}
