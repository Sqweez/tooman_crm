<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Checkout\CheckoutListResource;
use App\v2\Models\Checkout;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection {
        return CheckoutListResource::collection(
            Checkout::query()
                ->with('user')
                ->with('store')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return CheckoutListResource
     */
    public function store(Request $request): CheckoutListResource {
        $checkout = Checkout::create($request->all());
        $checkout->refresh();
        return new CheckoutListResource($checkout);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function destroy(int $id): Response {
        Checkout::whereKey($id)->delete();
        return response()->noContent();
    }
}
