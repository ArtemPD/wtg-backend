<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\Import\ImportOfferCollection;
use App\DTO\Import\ImportOfferDTO;
use App\DTO\Import\ImportPropertyDTO;
use App\Models\Import;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ImportPayloadCastTest extends TestCase
{
    use RefreshDatabase;

    private function makeOfferCollection(): ImportOfferCollection
    {
        return new ImportOfferCollection([
            new ImportOfferDTO(
                externalId: 'offer-a-10001',
                property: new ImportPropertyDTO(
                    code: 'BCN-0001',
                    name: 'Apartment near Sagrada Familia',
                    city: 'Barcelona',
                ),
                checkIn: CarbonImmutable::parse('2026-10-10'),
                checkOut: CarbonImmutable::parse('2026-10-15'),
                maxGuests: 4,
                price: 72500,
                currency: 'EUR',
                availableUnits: 2,
                expiresAt: CarbonImmutable::parse('2026-09-10T23:59:59Z'),
            ),
        ]);
    }

    #[Test]
    public function itReturnsATypedOfferCollectionAfterReload(): void
    {
        $import = Import::factory()->create([
            'payload' => $this->makeOfferCollection(),
        ]);

        $payload = $import->fresh()->payload;

        $this->assertInstanceOf(ImportOfferCollection::class, $payload);
        $this->assertCount(1, $payload);

        $offer = $payload->first();

        $this->assertInstanceOf(ImportOfferDTO::class, $offer);
        $this->assertInstanceOf(CarbonImmutable::class, $offer->checkIn);
        $this->assertInstanceOf(CarbonImmutable::class, $offer->checkOut);
        $this->assertInstanceOf(CarbonImmutable::class, $offer->expiresAt);
        $this->assertSame(72500, $offer->price);
        $this->assertSame('offer-a-10001', $offer->externalId);
        $this->assertSame('BCN-0001', $offer->property->code);
    }

    #[Test]
    public function itRejectsARawArrayWhenWritingThePayload(): void
    {
        $import = Import::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore-next-line assigning a raw array on purpose to prove the cast rejects it
        $import->payload = [['external_id' => 'offer-x']];
        $import->save();
    }

    #[Test]
    public function itRejectsAForeignCollectionTypeWhenWritingThePayload(): void
    {
        $import = Import::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore-next-line assigning a plain Collection on purpose to prove the cast rejects it
        $import->payload = collect([$this->makeOfferCollection()->first()]);
        $import->save();
    }
}
