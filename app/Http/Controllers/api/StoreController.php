<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Store;
use App\StoreType;
use App\User;
use App\UserRole;
use App\v2\Models\City;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        /* @var User $user */
        $user = auth()->user();
        if ($user) {
            $user->load('stores');
        }
        $storeQuery = Store::query()
            ->with('type')
            ->with('city_name')
            ->when($request->has('store_id'), function ($q) use ($request) {
                $q->where('id', $request->get('store_id'));
            })
            ->when($user && $user->stores->count() > 0, function ($q) use ($user) {
                $q->whereIn('id', $user->stores->pluck('id'));
            })
            ->get();
        return StoreResource::collection($storeQuery);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return StoreResource
     */
    public function store(Request $request)
    {
        return new StoreResource(Store::create($request->all()));
    }

    public function indexStores() {
        return StoreResource::collection(Store::where('type_id', 1)->get());
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Store $store
     * @return StoreResource
     */
    public function update(Request $request, Store $store)
    {
        $store->update($request->except(['balance', 'iron_balance', 'has_kaspi_terminal']));
        return new StoreResource($store);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Store $store
     * @return void
     */
    public function destroy(Store $store)
    {
        $store->delete();
    }

    public function types() {
        return StoreType::all();
    }

    public function getCities() {
        return City::all();
    }
}
