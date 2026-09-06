<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Property and Offer seeders are added in a later stage.
     */
    public function run(): void
    {
        $this->call(SupplierSeeder::class);
    }
}
