<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /** @var list<array{check_in: string, check_out: string}> */
    private const STAYS = [
        ['check_in' => '2026-10-10', 'check_out' => '2026-10-15'],
        ['check_in' => '2026-11-01', 'check_out' => '2026-11-07'],
    ];

    public function run(): void
    {
        $supplierA = Supplier::query()->where('code', 'supplier-a')->firstOrFail();
        $supplierB = Supplier::query()->where('code', 'supplier-b')->firstOrFail();

        foreach (Property::query()->get() as $property) {
            foreach (self::STAYS as $index => $stay) {
                $basePrice = 40000 + ($property->id * 1500) + ($index * 5000);

                $this->putOffer($supplierA, $property, $stay, $index, $basePrice);
                $this->putOffer($supplierB, $property, $stay, $index, $basePrice + 2500);
            }
        }
    }

    /** @param array{check_in: string, check_out: string} $stay */
    private function putOffer(Supplier $supplier, Property $property, array $stay, int $index, int $price): void
    {
        Offer::query()->updateOrCreate(
            [
                'supplier_id' => $supplier->id,
                'external_id' => sprintf('offer-%s-%d-%d', $supplier->code, $property->id, $index),
            ],
            [
                'property_id' => $property->id,
                'import_id' => null,
                'check_in' => $stay['check_in'],
                'check_out' => $stay['check_out'],
                'max_guests' => 4,
                'price' => $price,
                'currency' => 'EUR',
                'available_units' => 3,
                'expires_at' => now()->addMonths(6),
                'imported_at' => now(),
            ],
        );
    }
}
