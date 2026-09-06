<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /** @var list<array{code: string, name: string, city: string}> */
    private const PROPERTIES = [
        ['code' => 'BCN-0001', 'name' => 'Apartment near Sagrada Familia', 'city' => 'Barcelona'],
        ['code' => 'BCN-0002', 'name' => 'Loft in the Gothic Quarter', 'city' => 'Barcelona'],
        ['code' => 'BCN-0003', 'name' => 'Beachfront Studio Barceloneta', 'city' => 'Barcelona'],
        ['code' => 'MAD-0001', 'name' => 'Central Madrid Flat', 'city' => 'Madrid'],
        ['code' => 'MAD-0002', 'name' => 'Retiro Park View Apartment', 'city' => 'Madrid'],
        ['code' => 'MAD-0003', 'name' => 'Gran Via Penthouse', 'city' => 'Madrid'],
        ['code' => 'LIS-0001', 'name' => 'Alfama Traditional House', 'city' => 'Lisbon'],
        ['code' => 'LIS-0002', 'name' => 'Belem Riverside Apartment', 'city' => 'Lisbon'],
        ['code' => 'LIS-0003', 'name' => 'Baixa Modern Loft', 'city' => 'Lisbon'],
        ['code' => 'POR-0001', 'name' => 'Ribeira District Studio', 'city' => 'Porto'],
    ];

    public function run(): void
    {
        foreach (self::PROPERTIES as $property) {
            Property::query()->updateOrCreate(
                ['code' => $property['code']],
                ['name' => $property['name'], 'city' => $property['city']],
            );
        }
    }
}
