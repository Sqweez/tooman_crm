<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\ExcelService;
use App\Http\Resources\v2\Revision\RevisionProductResource;
use App\Http\Resources\v2\Revision\RevisionResource;
use App\Revision;
use App\RevisionProducts;
use App\v2\Models\ProductSku;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

class RevisionController extends Controller
{
    /**
     * @throws Exception
     */
    public function createRevision(Request $request, ExcelService $excelService) {
        ini_set('max_execution_time', 300);
        $store_id = $request->get('store_id');
        $user_id = $request->get('user_id', auth()->id());
        $revision = Revision::query()->create([
            'store_id' => $store_id,
            'user_id' => $user_id,
            'is_finished' => false,
        ]);

        $products = ProductSku::query()
            ->with(['batches' => function ($query) use ($store_id) {
                return $query->where('store_id', $store_id)->where('quantity', '>', 0);
            }])
            ->get();

        $products = collect($products)->map(function ($item) {
            $item['quantity'] = collect($item['batches'])->reduce(function ($a, $c) {
                return $a + $c['quantity'];
            }, 0);
            return $item;
        })->toArray();

        foreach ($products as $product) {
            RevisionProducts::query()->create([
                'revision_id' => $revision->id,
                'product_id' => $product['id'],
                'stock_quantity' => $product['quantity']
            ]);
        }

        $with = array_map(function ($item) {
            return 'revision_products.sku.' . $item;
        }, ProductSku::PRODUCT_SKU_WITH_CART_LIST);

        $revision = Revision::whereKey($revision->id)
            ->with(['store:id,name', 'user:id,name'])
            ->with($with)
            ->first();

        $products = collect(
            RevisionProductResource::collection($revision->revision_products)
        )->toArray();

        $path = $excelService->createRevisionFile($products);
        return response()->json([
            'path' => $path
        ]);
    }
}
