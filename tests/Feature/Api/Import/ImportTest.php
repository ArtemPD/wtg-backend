<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Import;

use App\Jobs\ProcessImportJob;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ImportTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function validPayload(): array
    {
        return [
            'supplier' => 'supplier-a',
            'external_import_id' => 'import-2026-09-01-001',
            'sent_at' => '2026-09-01T10:00:00Z',
            'offers' => [
                [
                    'external_id' => 'offer-a-10001',
                    'property' => [
                        'code' => 'BCN-0001',
                        'name' => 'Apartment near Sagrada Familia',
                        'city' => 'Barcelona',
                    ],
                    'check_in' => '2026-10-10',
                    'check_out' => '2026-10-15',
                    'max_guests' => 4,
                    'price' => 72500,
                    'currency' => 'EUR',
                    'available_units' => 2,
                    'expires_at' => '2026-09-10T23:59:59Z',
                ],
            ],
        ];
    }

    #[Test]
    public function itReturns202AndDispatchesTheJob(): void
    {
        Queue::fake();
        Supplier::factory()->create(['code' => 'supplier-a']);

        $response = $this->postJson(route('imports.store'), $this->validPayload());

        $response->assertStatus(202)
            ->assertJson(['data' => ['status' => 'pending']]);

        $this->assertDatabaseCount('imports', 1);
        Queue::assertPushed(ProcessImportJob::class);
    }

    #[Test]
    public function itReturns422WhenSupplierIsMissing(): void
    {
        $payload = $this->validPayload();
        unset($payload['supplier']);

        $this->postJson(route('imports.store'), $payload)->assertStatus(422);
    }

    #[Test]
    public function itReturns422ForAnUnknownSupplierCode(): void
    {
        $payload = $this->validPayload();
        $payload['supplier'] = 'does-not-exist';

        $this->postJson(route('imports.store'), $payload)->assertStatus(422);
    }

    #[Test]
    public function itReturns422WhenOffersIsEmpty(): void
    {
        Supplier::factory()->create(['code' => 'supplier-a']);

        $payload = $this->validPayload();
        $payload['offers'] = [];

        $this->postJson(route('imports.store'), $payload)->assertStatus(422);
    }

    #[Test]
    public function itReturns422WhenCheckOutIsNotAfterCheckIn(): void
    {
        Supplier::factory()->create(['code' => 'supplier-a']);

        $payload = $this->validPayload();
        $payload['offers'][0]['check_out'] = $payload['offers'][0]['check_in'];

        $this->postJson(route('imports.store'), $payload)->assertStatus(422);
    }

    #[Test]
    public function itReturns422WhenPriceIsNotAnInteger(): void
    {
        Supplier::factory()->create(['code' => 'supplier-a']);

        $payload = $this->validPayload();
        $payload['offers'][0]['price'] = 'not-a-number';

        $this->postJson(route('imports.store'), $payload)->assertStatus(422);
    }

    #[Test]
    public function itDoesNotDuplicateOrRedispatchOnARepeatedRequest(): void
    {
        Queue::fake();
        Supplier::factory()->create(['code' => 'supplier-a']);

        $first = $this->postJson(route('imports.store'), $this->validPayload());
        $second = $this->postJson(route('imports.store'), $this->validPayload());

        $first->assertStatus(202);
        $second->assertStatus(202);
        $this->assertSame($first->json('data.id'), $second->json('data.id'));

        $this->assertDatabaseCount('imports', 1);
        Queue::assertPushed(ProcessImportJob::class, 1);
    }
}
