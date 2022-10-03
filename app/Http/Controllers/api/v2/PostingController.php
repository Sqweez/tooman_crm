<?php

namespace App\Http\Controllers\api\v2;

use App\Actions\Posting\AcceptPostingAction;
use App\Actions\Posting\CreatePostingAction;
use App\Actions\Posting\DeclinePostingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\WriteOff\CreateWriteOffRequest;
use App\Http\Resources\Posting\PostingListResource;
use App\Http\Resources\Posting\SinglePostingResource;
use App\Posting;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostingController extends Controller
{
    public function index(): AnonymousResourceCollection {
        $writeOffs = Posting::query()
            ->with('user:id,name')
            ->with('store:id,name')
            ->latest()
            ->get();

        return PostingListResource::collection($writeOffs);
    }

    public function show(Posting $posting): SinglePostingResource {
        foreach (Posting::WITH_PRODUCTS as $WITH_PRODUCT) {
            $posting->load($WITH_PRODUCT);
        }
        $posting->load('user:id,name');
        $posting->load('store:id,name');
        return SinglePostingResource::make($posting);
    }

    public function store(CreateWriteOffRequest $request, CreatePostingAction $action): PostingListResource {
        $writeOff = $action->handle($request->validated());
        return PostingListResource::make($writeOff);
    }

    public function accept(Posting $posting, AcceptPostingAction $action): PostingListResource {
        $action->handle($posting);
        $posting->fresh();
        return PostingListResource::make($posting);
    }

    public function decline(Posting $posting, DeclinePostingAction $action): PostingListResource {
        $action->handle($posting);
        $posting->fresh();
        return PostingListResource::make($posting);
    }
}
