<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
final class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('???-####')),
            'name' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
        ];
    }
}
