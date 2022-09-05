<?php

namespace App\Actions\Revision;

use App\Http\Controllers\Services\ExcelService;
use App\Http\Resources\v2\Revision\RevisionProductResource;
use App\Http\Resources\v2\Revision\RevisionsListResource;
use App\Revision;
use App\RevisionProducts;
use App\Services\Revision\RevisionService;
use App\v2\Models\ProductSku;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CreateRevisionAction {

    /**
     * @throws Exception
     */
    public function handle(Request $request): array {
        $payload = [
            'store_id' => $request->get('store_id'),
            'user_id' => $request->get('user_id', auth()->id()),
            'is_finished' => false,
        ];
        $revision = $this->createRevision($payload);
        $products = $this->loadProducts($revision);
        $revisionFilePath = $this->createRevisionFile($products);
        $revision->update([
            'original_generated_revision_file' => $revisionFilePath
        ]);
        return [
            'path' => $revisionFilePath,
            'revision' => RevisionsListResource::make($revision)
        ];
    }

    private function createRevision(array $payload = []): Revision {
        $revision = Revision::create($payload);
        $this->createRevisionProducts($revision);
        return Revision::find($revision->id);
    }

    /**
     * @throws Exception
     */
    private function createRevisionFile(array $products): string {
        $template = ExcelService::loadExcelTemplate(Revision::FILE_TEMPLATE);
        $sheet = $template->getActiveSheet();
        $sheet->fromArray(
            $this->transformProducts($products),
            null,
            'A3',
            true
        );

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

        $sheet->getStyle('A2:E' . (count($products) + 2))
            ->applyFromArray($styleArray);

        foreach (range('A', 'E') as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }


        $excelWriter = new Xlsx($template);
        $fileName =  Revision::FILE_NAME . "_" . Carbon::today()->toDateString() . "_" . Str::random(10) . '.xlsx';
        $path = 'storage/excel/revisions/';
        \File::ensureDirectoryExists($path);
        $fullPath =  $path . $fileName;
        $excelWriter->save($fullPath);
        return $fullPath;
    }

    private function transformProducts(array $products): array {
        return array_map(function ($product) {
            return [
                'id' => $product['product_id'],
                'name' => $product['full_product_name'],
                'category' => $product['category'],
                'product_price' => number_format($product['product_price'], 2, ',', ','),
                'fact_quantity' => 0
            ];
        }, $products);
    }

    private function createRevisionProducts(Revision $revision) {
        $products = ProductSku::query()
            ->with(['batches' => function ($query) use ($revision) {
                return $query->where('store_id', $revision->store_id);
            }])
            ->with('product:id,product_price')
            ->get();

        foreach ($this->_transformProductCollection($products) as $product) {
            $revision->revision_products()->create([
                'product_id' => $product['id'],
                'stock_quantity' => $product['quantity'],
                'price' => $product['price'],
                'purchase_price' => $product['purchase_price']
            ]);
        }
    }

    private function loadProducts(Revision $revision): array {
        $products = RevisionService::loadRevisionProductWithNested($revision);
        return collect(RevisionProductResource::collection($products))
            ->sortBy('category_id')
            ->toArray();
    }

    private function _transformProductCollection($products): array {
        return collect($products)->map(function ($item) {
            $item['quantity'] = collect($item['batches'])->reduce(function ($a, $c) {
                return $a + $c['quantity'];
            }, 0);
            $item['price'] = $item['product']['product_price'];
            $item['purchase_price'] = collect($item['batches'])
                    ->sortByDesc('created_at')
                    ->first()['purchase_price'] ?? 0;
            return $item;
        })->toArray();
    }
}
