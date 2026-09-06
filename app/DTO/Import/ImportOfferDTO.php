<?php

declare(strict_types=1);

namespace App\DTO\Import;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class ImportOfferDTO implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $externalId,
        public ImportPropertyDTO $property,
        public CarbonImmutable $checkIn,
        public CarbonImmutable $checkOut,
        public int $maxGuests,
        public int $price,
        public string $currency,
        public int $availableUnits,
        public CarbonImmutable $expiresAt,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            externalId: (string)$data['external_id'],
            property: ImportPropertyDTO::fromArray($data['property']),
            checkIn: CarbonImmutable::parse($data['check_in']),
            checkOut: CarbonImmutable::parse($data['check_out']),
            maxGuests: (int)$data['max_guests'],
            price: (int)$data['price'],
            currency: (string)$data['currency'],
            availableUnits: (int)$data['available_units'],
            expiresAt: CarbonImmutable::parse($data['expires_at']),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'property' => $this->property->toArray(),
            'check_in' => $this->checkIn->toDateString(),
            'check_out' => $this->checkOut->toDateString(),
            'max_guests' => $this->maxGuests,
            'price' => $this->price,
            'currency' => $this->currency,
            'available_units' => $this->availableUnits,
            'expires_at' => $this->expiresAt->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
