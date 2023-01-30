<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportByProductsRequest;
use App\Sale;
use App\SaleProduct;
use App\v2\Models\ProductSku;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getReportByProducts(ReportByProductsRequest $request) {
        $validatedFilters = $request->validated();
        $reportBySku = SaleProduct::query()
            ->whereHas('sale', function ($query) use ($validatedFilters) {
                $query
                    ->whereDate('created_at', '>=', $validatedFilters['start'])
                    ->whereDate('created_at', '<=', $validatedFilters['finish'])
                    ->where('is_confirmed', true);
                if (isset($validatedFilters['seller_id'])) {
                    $query->where('user_id', $validatedFilters['seller_id']);
                }
                if (isset($validatedFilters['store_id'])) {
                    $query->where('store_id', $validatedFilters['store_id']);
                }
            })
            ->with([
                'product',
                'product.product:id,product_name,product_price,manufacturer_id,category_id,subcategory_id',
                'product.product.attributes:attribute_value',
                'product.attributes:attribute_value',
                'product.product.manufacturer'
            ])
            ->get()
            ->groupBy('product_id')
            ->map(function ($sale, $product_id) {
                $totalPurchasePrice = (collect($sale)->reduce(function ($a, $c) {
                    return $a + $c['purchase_price'];
                }, 0));
                $totalProductPrice = ceil(collect($sale)->reduce(function ($a, $c) {
                    return $a + $c->final_sale_price;
                }, 0));
                /* @var ProductSku $sku */
                $sku = $sale->first()->product;
                $product = $sku->product;
                return [
                    'total_purchase_price' => $totalPurchasePrice,
                    'total_product_price' => $totalProductPrice,
                    'total_margin' => $totalProductPrice > 0 ? $totalProductPrice - $totalPurchasePrice : 0,
                    'total_count' => count($sale),
                    'id' => $product_id,
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                    'subcategory_id' => $product->subcategory_id,
                    'manufacturer_id' => $product->manufacturer_id,
                    'product_name' => $product->product_name,
                    'manufacturer' => $product->manufacturer->manufacturer_name,
                    'all_attributes' => $sku->attributes->mergeRecursive($product->attributes),
                    'product_attributes' => $product->attributes,
                    'sku_attributes' => $sku->attributes,
                ];
            })
            ->values()
            ->map(function ($sku) {
                /* @var ProductSku $sku */
                $additionalAttributes = collect($sku['all_attributes'])->pluck('attribute_value')->join('|');
                $productName = $sku['product_name'];
                if (strlen($additionalAttributes)) {
                    $productName .= ', ' . $additionalAttributes;
                }
                $sku['product_name_full'] = $productName;
                return $sku;
            })
            ->sortBy('product_name_full')
            ->values();

        $reportByProducts = $reportBySku
            ->groupBy('product_id')
            ->map(function ($products, $id) {
                $totalPurchasePrice = collect($products)->reduce(function ($a, $c) {
                    return $a + $c['total_purchase_price'];
                }, 0);
                $totalProductPrice = collect($products)->reduce(function ($a, $c) {
                    return $a + $c['total_product_price'];
                }, 0);
                $totalCount = collect($products)->reduce(function ($a, $c) {
                    return $a + $c['total_count'];
                }, 0);
                $product = $products->first();
                return [
                    'total_purchase_price' => $totalPurchasePrice,
                    'total_product_price' => $totalProductPrice,
                    'total_margin' => $totalProductPrice > 0 ? $totalProductPrice - $totalPurchasePrice : 0,
                    'total_count' => $totalCount,
                    'id' => $id,
                    'category_id' => $product['category_id'],
                    'subcategory_id' => $product['subcategory_id'],
                    'manufacturer_id' => $product['manufacturer_id'],
                    'product_name' => $product['product_name'],
                    'manufacturer' => $product['manufacturer'],
                    'product_attributes' => $product['product_attributes'],
                ];
            })
            ->values()
            ->map(function ($sku) {
                /* @var ProductSku $sku */
                $additionalAttributes = collect($sku['product_attributes'])->pluck('attribute_value')->join('|');
                $productName = $sku['product_name'];
                if (strlen($additionalAttributes)) {
                    $productName .= ', ' . $additionalAttributes;
                }
                $sku['product_name_full'] = $productName;
                return $sku;
            })
            ->sortBy('product_name_full')
            ->values();

        return [
            'report_by_sku' => $reportBySku,
            'report_by_products' => $reportByProducts
        ];
    }
}
