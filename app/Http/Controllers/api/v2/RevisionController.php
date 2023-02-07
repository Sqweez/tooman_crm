<?php

namespace App\Http\Controllers\api\v2;

use App\Actions\Posting\CreatePostingAction;
use App\Actions\Revision\CreateRevisionAction;
use App\Actions\Revision\EditRevisionAction;
use App\Actions\Revision\FinishRevisionAction;
use App\Actions\Revision\GenerateRevisionPivotTableAction;
use App\Actions\Revision\RollbackRevisionAction;
use App\Actions\Revision\SendRevisionToApprovementAction;
use App\Actions\WriteOff\CreateWriteOffAction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\ExcelService;
use App\Http\Resources\v2\Revision\RevisionProductResource;
use App\Http\Resources\v2\Revision\RevisionsListResource;
use App\Jobs\Revision\EditRevisionJob;
use App\Jobs\Revision\RollbackRevisionJob;
use App\Jobs\Revision\SendRevisionToApprove;
use App\Revision;
use App\Services\Revision\RevisionService;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

class RevisionController extends Controller
{
    /**
     * @throws Exception
     */
    public function createRevision(Request $request, CreateRevisionAction $action): JsonResponse {
        return response()->json(
            $action->handle($request)
        );
    }

    public function show(Revision $revision): array {
        $revision->load('writeOff');
        $revision->load('posting');
        return [
            'revision' => RevisionsListResource::make($revision),
            'products' => RevisionProductResource::collection(RevisionService::loadRevisionProductWithNested($revision, false))
        ];
    }

    public function destroy($id): array {
        Revision::whereKey($id)->delete();
        return [];
    }

    public function index(Request $request): AnonymousResourceCollection {
        /* @var User $user */
        $user = auth()->user();
        $user->load('stores');
        $isSuperUser = $user && $user->is_super_user;
        $revisions = Revision::query()
            ->when(!$isSuperUser, function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->when($user->isGeneralManager(), function ($q) use ($user) {
                return $q->whereIn('store_id', $user->stores->pluck('id'));
            })
            ->with('writeOff')
            ->with('posting')
            ->latest()
            ->get();

        return RevisionsListResource::collection($revisions);
    }

    /**
     */
    public function sendToApprove(Revision $revision, Request $request) {
        $filePath = \Storage::putFileAs(
            'public/excel/revisions',
            $request->file('file'),
            ExcelService::generateExcelName('ФАЙЛ_РЕВИЗИИ')
        );
        SendRevisionToApprove::dispatch(
            $revision,
            str_replace('storage/public/', '', $filePath)
        );
        $revision->setInProcess();
        return response([
            'message' => 'Ревизия была отправлена на проверку!'
        ]);
    }

    /**
     * @throws Exception
     */
    public function generatePivotTable(Revision $revision, GenerateRevisionPivotTableAction $action): array {
        return [
            'path' => $action->handle($revision),
            'revision' => RevisionsListResource::make($revision->fresh())
        ];
    }

    public function editRevision(Request $request, Revision $revision) {
        $filePath = \Storage::putFileAs(
            'public/excel/revisions',
            $request->file('file'),
            ExcelService::generateExcelName('ИСПРАВЛЕНИЕ_РЕВИЗИИ')
        );
        EditRevisionJob::dispatch(
            $revision,
            str_ireplace('public/', '', $filePath)
        );

        $revision->setInProcess();

        return response()->noContent();
    }

    /**
     */
    public function rollbackRevision(Revision $revision): \Illuminate\Http\Response {
        RollbackRevisionJob::dispatch($revision);
        $revision->setInProcess();
        return response()->noContent();
    }

    /**
     * @throws Exception
     */
    public function finishRevision(Revision $revision, FinishRevisionAction $action): array {
        return [
            'path' => $action->handle($revision),
            'revision' => RevisionsListResource::make($revision->fresh())
        ];
    }

    public function createWriteOff(Revision $revision, CreateWriteOffAction $action): \Illuminate\Http\Response {
        $writeOffRequest = [
            'user_id' => auth()->id(),
            'store_id' => $revision->store_id,
            'description' => 'Списание на основании ревизии',
            'revision_id' => $revision->id,
            'products' => $revision
                ->revision_products
                ->filter(function ($product) {
                    return $product['fact_quantity'] < $product['stock_quantity'];
                })
                ->map(function ($product) {
                    return [
                        'id' => $product->product_id,
                        'quantity' => $product['stock_quantity'] - $product['fact_quantity'],
                        'product_price' => $product['price']
                    ];
            })
                ->toArray()
        ];

        $action->handle($writeOffRequest);
        return response()->noContent();
    }

    public function createPosting(Revision $revision, CreatePostingAction $action): \Illuminate\Http\Response {
        $postingRequest = [
            'user_id' => auth()->id(),
            'store_id' => $revision->store_id,
            'description' => 'Списание на основании ревизии',
            'revision_id' => $revision->id,
            'products' => $revision
                ->revision_products
                ->filter(function ($product) {
                    return $product['fact_quantity'] > $product['stock_quantity'];
                })
                ->map(function ($product) {
                    return [
                        'id' => $product->product_id,
                        'quantity' => $product['fact_quantity'] - $product['stock_quantity'],
                        'purchase_price' => $product['purchase_price']
                    ];
                })
                ->toArray()
        ];
        $action->handle($postingRequest);
        return response()->noContent();
    }
}
