<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Import;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ImportStatusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itReturns200WithTheFullImportStructure(): void
    {
        $supplier = Supplier::factory()->create(['code' => 'supplier-a']);
        $import = Import::factory()->create([
            'supplier_id' => $supplier->id,
            'external_import_id' => 'import-2026-09-01-001',
            'status' => ImportStatus::COMPLETED,
            'total_offers' => 20,
            'processed_offers' => 20,
            'error' => null,
            'completed_at' => now(),
        ]);

        $response = $this->getJson(route('imports.show', $import));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $import->id,
                    'supplier' => 'supplier-a',
                    'external_import_id' => 'import-2026-09-01-001',
                    'status' => 'completed',
                    'total_offers' => 20,
                    'processed_offers' => 20,
                    'error' => null,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'supplier',
                    'external_import_id',
                    'sent_at',
                    'status',
                    'total_offers',
                    'processed_offers',
                    'error',
                    'created_at',
                    'completed_at',
                ],
            ]);
    }

    #[Test]
    public function itReturns404ForANonExistentImport(): void
    {
        $this->getJson(route('imports.show', ['import' => 999999]))->assertStatus(404);
    }
}
