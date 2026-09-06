<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Property;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PropertySearchTest extends TestCase
{
    use RefreshDatabase;

    private const CHECK_IN = '2026-10-10';

    private const CHECK_OUT = '2026-10-15';

    /** @return array<string, mixed> */
    private function searchParams(array $overrides = []): array
    {
        return array_merge([
            'check_in' => self::CHECK_IN,
            'check_out' => self::CHECK_OUT,
            'guests' => 2,
        ], $overrides);
    }

    /** @return array<string, mixed> */
    private function validOfferAttributes(array $overrides = []): array
    {
        return array_merge([
            'check_in' => self::CHECK_IN,
            'check_out' => self::CHECK_OUT,
            'max_guests' => 4,
            'available_units' => 2,
            'expires_at' => now()->addWeek(),
            'currency' => 'EUR',
        ], $overrides);
    }

    #[Test]
    public function itExcludesOffersWithDifferentDates(): void
    {
        $property = Property::factory()->create();
        Offer::factory()->for($property)->create($this->validOfferAttributes([
            'check_in' => '2026-11-01',
            'check_out' => '2026-11-05',
        ]));

        $response = $this->getJson(route('properties.index', $this->searchParams()));

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    #[Test]
    public function itExcludesOffersBelowRequestedGuestCapacity(): void
    {
        $property = Property::factory()->create();
        Offer::factory()->for($property)->create($this->validOfferAttributes(['max_guests' => 1]));

        $response = $this->getJson(route('properties.index', $this->searchParams(['guests' => 2])));

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    #[Test]
    public function itExcludesSoldOutOffers(): void
    {
        $property = Property::factory()->create();
        Offer::factory()->for($property)->create($this->validOfferAttributes(['available_units' => 0]));

        $response = $this->getJson(route('properties.index', $this->searchParams()));

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    #[Test]
    public function itExcludesExpiredOffers(): void
    {
        $property = Property::factory()->create();
        Offer::factory()->for($property)->create($this->validOfferAttributes(['expires_at' => now()->subDay()]));

        $response = $this->getJson(route('properties.index', $this->searchParams()));

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    #[Test]
    public function itExcludesOffersInADifferentCity(): void
    {
        $property = Property::factory()->create(['city' => 'Madrid']);
        Offer::factory()->for($property)->create($this->validOfferAttributes());

        $response = $this->getJson(route('properties.index', $this->searchParams(['city' => 'Barcelona'])));

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    #[Test]
    public function itReturnsTheCheapestOfferAcrossSuppliers(): void
    {
        $property = Property::factory()->create();
        $cheapSupplier = Supplier::factory()->create();
        $expensiveSupplier = Supplier::factory()->create();

        Offer::factory()->for($property)->for($expensiveSupplier)->create(
            $this->validOfferAttributes(['price' => 90000]),
        );
        Offer::factory()->for($property)->for($cheapSupplier)->create(
            $this->validOfferAttributes(['price' => 50000]),
        );

        $response = $this->getJson(route('properties.index', $this->searchParams()));

        $response->assertStatus(200)
            ->assertJsonPath('data.0.best_offer.price', 50000)
            ->assertJsonPath('data.0.best_offer.supplier', $cheapSupplier->code);
    }

    #[Test]
    public function itSortsPropertiesByPriceAscending(): void
    {
        $expensiveProperty = Property::factory()->create();
        Offer::factory()->for($expensiveProperty)->create($this->validOfferAttributes(['price' => 90000]));

        $cheapProperty = Property::factory()->create();
        Offer::factory()->for($cheapProperty)->create($this->validOfferAttributes(['price' => 30000]));

        $response = $this->getJson(route('properties.index', $this->searchParams()));

        $response->assertStatus(200)
            ->assertJsonPath('data.0.code', $cheapProperty->code)
            ->assertJsonPath('data.1.code', $expensiveProperty->code);
    }

    #[Test]
    public function itPaginatesWithSimplePaginateLinksAndMeta(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $property = Property::factory()->create();
            Offer::factory()->for($property)->create($this->validOfferAttributes(['price' => 10000 + $i]));
        }

        $firstPage = $this->getJson(route('properties.index', $this->searchParams(['per_page' => 2])));

        $firstPage->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('links.prev', null)
            ->assertJsonPath('links.next', fn (?string $next): bool => $next !== null);

        $secondPage = $this->getJson(route('properties.index', $this->searchParams(['per_page' => 2, 'page' => 2])));

        $secondPage->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('links.next', null);
    }

    #[Test]
    public function itIssuesExactlyOneQueryRegardlessOfResultCount(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $property = Property::factory()->create();
            Offer::factory()->for($property)->create($this->validOfferAttributes());
        }

        $queryCount = 0;
        DB::listen(function () use (&$queryCount): void {
            $queryCount++;
        });

        $this->getJson(route('properties.index', $this->searchParams()))->assertStatus(200);

        $this->assertSame(1, $queryCount);

        for ($i = 0; $i < 10; $i++) {
            $property = Property::factory()->create();
            Offer::factory()->for($property)->create($this->validOfferAttributes());
        }

        $queryCountAfterMoreData = 0;
        DB::listen(function () use (&$queryCountAfterMoreData): void {
            $queryCountAfterMoreData++;
        });

        $this->getJson(route('properties.index', $this->searchParams(['per_page' => 100])))->assertStatus(200);

        $this->assertSame(1, $queryCountAfterMoreData);
    }
}
