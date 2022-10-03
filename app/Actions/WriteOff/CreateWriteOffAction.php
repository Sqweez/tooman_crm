<?php

namespace App\Actions\WriteOff;

use App\Http\Requests\WriteOff\CreateWriteOffRequest;
use App\v2\Models\WriteOff;
use Illuminate\Http\Request;

class CreateWriteOffAction {

    public function handle(array $request): WriteOff {
        $writeOff = $this->createWriteOff(\Arr::except($request, ['products']));
        $this->createWriteOffProducts($writeOff, $request['products']);
        return $writeOff;
    }

    private function createWriteOff($payload): WriteOff {
        $writeOff = WriteOff::create($payload);
        return WriteOff::find($writeOff->id);
    }

    private function createWriteOffProducts(WriteOff $writeOff, array $products) {
        collect($products)->each(function ($product) use ($writeOff) {
            $writeOff->items()->create([
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
                'product_price' => $product['product_price'],
            ]);
        });
    }
}
