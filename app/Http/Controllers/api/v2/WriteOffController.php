<?php

namespace App\Http\Controllers\api\v2;

use App\Actions\WriteOff\AcceptWriteOffAction;
use App\Actions\WriteOff\CreateWriteOffAction;
use App\Actions\WriteOff\DeclineWriteOffAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\WriteOff\CreateWriteOffRequest;
use App\Http\Resources\WriteOff\SingleWriteOffResource;
use App\Http\Resources\WriteOff\WriteOffListResource;
use App\v2\Models\WriteOff;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WriteOffController extends Controller
{
    public function index(): AnonymousResourceCollection {
        $writeOffs = WriteOff::query()
            ->with('user:id,name')
            ->with('store:id,name')
            ->latest()
            ->get();

        return WriteOffListResource::collection($writeOffs);
    }

    public function show(WriteOff $writeOff) {
        foreach (WriteOff::WITH_PRODUCTS as $WITH_PRODUCT) {
            $writeOff->load($WITH_PRODUCT);
        }
        $writeOff->load('user:id,name');
        $writeOff->load('store:id,name');
        return SingleWriteOffResource::make($writeOff);
    }

    public function store(CreateWriteOffRequest $request, CreateWriteOffAction $action): WriteOffListResource {
        $writeOff = $action->handle($request->validated());
        return WriteOffListResource::make($writeOff);
    }

    public function accept(WriteOff $writeOff, AcceptWriteOffAction $action): WriteOffListResource {
        $action->handle($writeOff);
        $writeOff->fresh();
        return WriteOffListResource::make($writeOff);
    }

    public function decline(WriteOff $writeOff, DeclineWriteOffAction $action): WriteOffListResource {
        $action->handle($writeOff);
        $writeOff->fresh();
        return WriteOffListResource::make($writeOff);
    }
}
