<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
final class OfferFactory extends Factory
{
    protected $model = Offer::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $checkIn = CarbonImmutable::instance($this->faker->dateTimeBetween('+1 week', '+3 months'));
        $checkOut = $checkIn->addDays($this->faker->numberBetween(2, 14));

        return [
            'supplier_id' => Supplier::factory(),
            'property_id' => Property::factory(),
            'import_id' => null,
            'external_id' => $this->faker->unique()->uuid(),
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'max_guests' => $this->faker->numberBetween(1, 8),
            'price' => $this->faker->numberBetween(5000, 100000),
            'currency' => 'EUR',
            'available_units' => $this->faker->numberBetween(1, 5),
            'expires_at' => now()->addWeek(),
            'imported_at' => now(),
        ];
    }

    /** Offer whose expires_at is in the past. */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /** Offer with no remaining units. */
    public function soldOut(): static
    {
        return $this->state(fn (array $attributes): array => [
            'available_units' => 0,
        ]);
    }
}
