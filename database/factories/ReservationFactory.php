<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Offer;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
final class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'client_reference' => $this->faker->unique()->uuid(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'price' => $this->faker->numberBetween(5000, 100000),
            'currency' => 'EUR',
            'check_in' => $this->faker->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'check_out' => $this->faker->dateTimeBetween('+2 months', '+3 months')->format('Y-m-d'),
        ];
    }
}
