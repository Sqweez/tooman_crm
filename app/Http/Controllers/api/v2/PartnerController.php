<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use App\UserRole;
use App\v2\Models\PartnerProduct;
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
}
