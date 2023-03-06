<?php

namespace App\Actions\Arrival;

use App\Arrival;

class CreateArrivalAction {

    /**
     * @throws \Exception
     */
    public function handle(array $payload): Arrival {
        if (isset($payload['arrival_id'])) {
            $arrival = Arrival::find($payload['arrival_id']);
            if ($arrival) {
                $arrival->products()->delete();
                $arrival->delete();
            }
        }
        $arrival = Arrival::create(\Arr::except($payload, ['products', 'arrival_id']));
        collect($payload['products'])->each(function ($product) use ($arrival) {
            $arrival->products()->create([
                'product_id' => $product['id'],
                'count' => $product['count'],
                'purchase_price' => $product['purchase_price']
            ]);
        });
        return $arrival;
    }
}
