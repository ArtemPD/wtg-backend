<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\DTO\Import\ImportOfferCollection;
use App\Enums\ImportStatus;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use App\Services\ImportProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Throwable;

final class ProcessImportTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<int, array<string, mixed>> $offers */
    private function makeImport(array $offers, ImportStatus $status = ImportStatus::PENDING): Import
    {
        return Import::factory()->create([
            'status' => $status,
            'total_offers' => count($offers),
            'payload' => ImportOfferCollection::fromArray($offers),
        ]);
    }

    /** @return array<string, mixed> */
    private function offerPayload(array $overrides = []): array
    {
        return array_merge([
            'external_id' => 'offer-a-10001',
            'property' => ['code' => 'BCN-0001', 'name' => 'Apartment near Sagrada Familia', 'city' => 'Barcelona'],
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 4,
            'price' => 72500,
            'currency' => 'EUR',
            'available_units' => 2,
            'expires_at' => '2026-09-10T23:59:59Z',
        ], $overrides);
    }

    #[Test]
    public function itCreatesPropertiesAndOffersAndCompletesTheImport(): void
    {
        $import = $this->makeImport([$this->offerPayload()]);

        (new ProcessImportJob($import))->handle(new ImportProcessor());

        $import->refresh();

        $this->assertSame(ImportStatus::COMPLETED, $import->status);
        $this->assertSame(1, $import->processed_offers);
        $this->assertNotNull($import->completed_at);
        $this->assertDatabaseHas('properties', ['code' => 'BCN-0001', 'city' => 'Barcelona']);
        $this->assertDatabaseHas('offers', [
            'external_id' => 'offer-a-10001',
            'supplier_id' => $import->supplier_id,
            'price' => 72500,
        ]);
    }

    #[Test]
    public function itDoesNotDuplicateAPropertyWithAnExistingCode(): void
    {
        Property::factory()->create(['code' => 'BCN-0001', 'name' => 'Old Name', 'city' => 'Old City']);

        $import = $this->makeImport([$this->offerPayload()]);

        (new ProcessImportJob($import))->handle(new ImportProcessor());

        $this->assertSame(1, Property::query()->where('code', 'BCN-0001')->count());
        $this->assertDatabaseHas('properties', [
            'code' => 'BCN-0001',
            'name' => 'Apartment near Sagrada Familia',
            'city' => 'Barcelona',
        ]);
    }

    #[Test]
    public function itUpdatesAnExistingOfferFromADifferentImport(): void
    {
        $import = $this->makeImport([$this->offerPayload()]);
        $existingOffer = Offer::factory()->create([
            'supplier_id' => $import->supplier_id,
            'external_id' => 'offer-a-10001',
            'price' => 10000,
            'available_units' => 1,
        ]);

        (new ProcessImportJob($import))->handle(new ImportProcessor());

        $this->assertSame(1, Offer::query()->where('supplier_id', $import->supplier_id)
            ->where('external_id', 'offer-a-10001')
            ->count());

        $updatedOffer = Offer::query()->findOrFail($existingOffer->id);

        $this->assertSame(72500, $updatedOffer->price);
        $this->assertSame(2, $updatedOffer->available_units);
        $this->assertSame($import->id, $updatedOffer->import_id);
    }

    #[Test]
    public function itMarksTheImportAsFailedWhenProcessingThrows(): void
    {
        $import = $this->makeImport([
            $this->offerPayload(['check_in' => '2026-10-15', 'check_out' => '2026-10-10']),
        ]);

        try {
            (new ProcessImportJob($import))->handle(new ImportProcessor());
            $this->fail('Expected processing to throw because check_out is before check_in.');
        } catch (Throwable) {
            // expected
        }

        $import->refresh();

        $this->assertSame(ImportStatus::FAILED, $import->status);
        $this->assertNotNull($import->error);
    }

    #[Test]
    public function itDoesNothingWhenRerunningAnAlreadyCompletedImport(): void
    {
        $import = $this->makeImport([$this->offerPayload()], ImportStatus::COMPLETED);
        $import->update(['processed_offers' => 1, 'completed_at' => now()]);

        (new ProcessImportJob($import->fresh()))->handle(new ImportProcessor());

        $this->assertDatabaseMissing('offers', ['external_id' => 'offer-a-10001']);
        $this->assertDatabaseMissing('properties', ['code' => 'BCN-0001']);
    }
}
