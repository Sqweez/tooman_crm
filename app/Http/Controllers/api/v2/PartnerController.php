<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\v2\Product\ProductResource;
use App\Http\Resources\v2\Product\ProductsResource;
use App\Sale;
use App\User;
use App\UserRole;
use App\v2\Models\PartnerProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class PartnerController extends Controller
{
    public function index(): AnonymousResourceCollection {
        return UserResource::collection(
            User::query()
                ->partners()
                ->with(['store.city_name', 'role'])
                ->get()
        );
    }

    public function getPartnerProductIds(User $user): Collection {
        return PartnerProduct::query()->where('user_id', $user->id)->get()->pluck('product_sku_id');
    }

    public function syncPartnerProducts(User $user, Request $request): JsonResponse {
        $products = $request->get('products', []);
        PartnerProduct::query()->where('user_id', $user->id)->delete();
        foreach ($products as $product) {
            PartnerProduct::query()->create(array_merge_recursive($product, ['user_id' => $user->id]));
        }
        return response()->json([]);
    }

    public function show(User $user, Request $request) {
        $start = Carbon::parse($request->get('start'))->startOfDay();
        $finish = Carbon::parse($request->get('finish'))->endOfDay();
        $productIds = PartnerProduct::query()
            ->where('user_id', $user->id)
            ->get()
            ->pluck('product_sku_id');

        return Sale::query()
            ->with(['products' => function ($q) use ($productIds) {
                return $q->whereIn('product_id', $productIds);
            }])
            ->whereHas('products', function ($q) use ($productIds) {
                return $q->whereIn('product_id', $productIds);
            })
            ->with(['products' => function ($q) use ($productIds) {
                return $q
                    ->whereIn('product_id', $productIds)
                    ->with(['product', 'product.product.category', 'product.product.subcategory'])
                    ->with(['product.product:id,product_name,manufacturer_id'])
                    ->with(['product.product.manufacturer', 'product.product.attributes', 'product.attributes']);
            }])
            ->reportDate([$start, $finish])
            ->get()
            ->pluck('products')
            ->flatten(1)
            ->groupBy('product_id')
            ->map(function ($products, $product_id) {
                return [
                    'product_id' => $product_id,
                    'count' => count($products),
                    'product' => new ProductsResource($products->first()->product),
                    'total_purchase_price' => collect($products)->reduce(function ($a, $c) {
                        return $a + $c->purchase_price;
                    }, 0)
                ];
            })
            ->values()
            ->sortBy('product.product_name')
            ->values();
    }
}
