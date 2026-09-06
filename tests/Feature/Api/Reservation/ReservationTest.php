<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Reservation;

use App\Models\Offer;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'client_reference' => 'web-order-9f782b1c',
            'customer_name' => 'John Smith',
            'customer_email' => 'john@example.com',
        ], $overrides);
    }

    #[Test]
    public function itReturns201AndDecrementsAvailableUnits(): void
    {
        $offer = Offer::factory()->create(['available_units' => 2, 'price' => 72500, 'currency' => 'EUR']);

        $response = $this->postJson(route('offers.reservations.store', $offer), $this->payload());

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'offer_id' => $offer->id,
                    'client_reference' => 'web-order-9f782b1c',
                    'customer_name' => 'John Smith',
                    'customer_email' => 'john@example.com',
                    'price' => 72500,
                    'currency' => 'EUR',
                ],
            ]);

        $this->assertSame(1, $offer->fresh()->available_units);
        $this->assertDatabaseHas('reservations', [
            'offer_id' => $offer->id,
            'client_reference' => 'web-order-9f782b1c',
            'price' => 72500,
            'currency' => 'EUR',
        ]);
    }

    #[Test]
    public function itReturns409OnTheSecondAttemptForTheLastUnit(): void
    {
        $offer = Offer::factory()->create(['available_units' => 1]);

        $first = $this->postJson(route('offers.reservations.store', $offer), $this->payload());
        $second = $this->postJson(
            route('offers.reservations.store', $offer),
            $this->payload(['client_reference' => 'web-order-second']),
        );

        $first->assertStatus(201);
        $second->assertStatus(409)->assertJson(['message' => 'Offer is sold out.']);

        $this->assertSame(0, $offer->fresh()->available_units);
    }

    #[Test]
    public function itReturns409ForAnExpiredOffer(): void
    {
        $offer = Offer::factory()->expired()->create();

        $response = $this->postJson(route('offers.reservations.store', $offer), $this->payload());

        $response->assertStatus(409)->assertJson(['message' => 'Offer has expired.']);
    }

    #[Test]
    public function itReturns409ForADuplicateClientReference(): void
    {
        $firstOffer = Offer::factory()->create(['available_units' => 5]);
        $secondOffer = Offer::factory()->create(['available_units' => 5]);

        $first = $this->postJson(
            route('offers.reservations.store', $firstOffer),
            $this->payload(['client_reference' => 'duplicate-ref']),
        );
        $second = $this->postJson(
            route('offers.reservations.store', $secondOffer),
            $this->payload(['client_reference' => 'duplicate-ref']),
        );

        $first->assertStatus(201);
        $second->assertStatus(409)
            ->assertJson(['message' => 'Reservation already exists for this client reference.']);

        $this->assertSame(1, Reservation::query()->where('client_reference', 'duplicate-ref')->count());
    }

    #[Test]
    public function itReturns422ForAnInvalidEmail(): void
    {
        $offer = Offer::factory()->create();

        $response = $this->postJson(
            route('offers.reservations.store', $offer),
            $this->payload(['customer_email' => 'not-an-email']),
        );

        $response->assertStatus(422);
    }

    #[Test]
    public function itReturns404ForANonExistentOffer(): void
    {
        $this->postJson(
            route('offers.reservations.store', ['offer' => 999999]),
            $this->payload(),
        )->assertStatus(404);
    }
}
