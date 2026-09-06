<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Import>
 */
final class ImportFactory extends Factory
{
    protected $model = Import::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'external_import_id' => $this->faker->unique()->uuid(),
            'sent_at' => now(),
            'status' => ImportStatus::PENDING,
            'total_offers' => 0,
            'processed_offers' => 0,
            'error' => null,
            'completed_at' => null,
        ];
    }
}
