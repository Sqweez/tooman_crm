<?php

namespace App\Actions\Posting;

use App\Http\Requests\WriteOff\CreateWriteOffRequest;
use App\Posting;
use App\v2\Models\WriteOff;
use Illuminate\Http\Request;

class CreatePostingAction {

    public function handle(array $request): Posting {
        $posting = $this->createPosting(\Arr::except($request, ['products']));
        $this->createProducts($posting, $request['products']);
        return $posting;
    }

    private function createPosting($payload): Posting {
        $posting = Posting::create($payload);
        return Posting::find($posting->id);
    }

    private function createProducts(Posting $posting, array $products) {
        collect($products)->each(function ($product) use ($posting) {
            $posting->items()->create([
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
                'purchase_price' => $product['purchase_price'],
            ]);
        });
    }
}
