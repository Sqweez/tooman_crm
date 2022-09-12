<?php

namespace App\Http\Controllers\api\v2;

use App\Actions\WriteOff\CreateWriteOffAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\WriteOff\CreateWriteOffRequest;
use App\Http\Resources\WriteOff\WriteOffListResource;
use App\v2\Models\WriteOff;

class WriteOffController extends Controller
{
    public function index() {
        $writeOffs = WriteOff::query()
            ->with(WriteOff::WITH_PRODUCTS)
            ->get();

        return WriteOffListResource::collection($writeOffs);
    }

    public function store(CreateWriteOffRequest $request, CreateWriteOffAction $action): WriteOff {
        $writeOff = $action->handle($request);
        return $writeOff;
    }
}
