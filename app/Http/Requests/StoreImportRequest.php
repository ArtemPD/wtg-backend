<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\Import\ImportOfferCollection;
use Illuminate\Foundation\Http\FormRequest;

final class StoreImportRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'supplier' => ['required', 'string', 'exists:suppliers,code'],
            'external_import_id' => ['required', 'string', 'max:190'],
            'sent_at' => ['required', 'date'],
            'offers' => ['required', 'array', 'min:1'],
            'offers.*.external_id' => ['required', 'string', 'max:190', 'distinct'],
            'offers.*.property.code' => ['required', 'string', 'max:64'],
            'offers.*.property.name' => ['required', 'string', 'max:255'],
            'offers.*.property.city' => ['required', 'string', 'max:120'],
            'offers.*.check_in' => ['required', 'date_format:Y-m-d'],
            'offers.*.check_out' => ['required', 'date_format:Y-m-d', 'after:offers.*.check_in'],
            'offers.*.max_guests' => ['required', 'integer', 'min:1', 'max:255'],
            'offers.*.price' => ['required', 'integer', 'min:0'],
            'offers.*.currency' => ['required', 'string', 'size:3'],
            'offers.*.available_units' => ['required', 'integer', 'min:0'],
            'offers.*.expires_at' => ['required', 'date'],
        ];
    }

    /**
     * Get the request's body parameters.
     *
     * @return array<string, mixed>
     */
    public function bodyParameters(): array
    {
        return [
            'supplier' => [
                'description' => 'Supplier code.',
                'example' => 'supplier-a',
            ],
            'external_import_id' => [
                'description' => 'Import identifier assigned by the supplier.',
                'example' => 'import-2026-09-01-001',
            ],
            'sent_at' => [
                'description' => 'When the supplier generated this import (ISO-8601).',
                'example' => '2026-09-01T10:00:00Z',
            ],
            'offers' => [
                'description' => 'List of offers included in this import.',
            ],
            'offers.*.external_id' => [
                'description' => 'Offer identifier assigned by the supplier.',
                'example' => 'offer-a-10001',
            ],
            'offers.*.property.code' => [
                'description' => 'Canonical property code shared across suppliers.',
                'example' => 'BCN-0001',
            ],
            'offers.*.property.name' => [
                'description' => 'Property name.',
                'example' => 'Apartment near Sagrada Familia',
            ],
            'offers.*.property.city' => [
                'description' => 'City the property is located in.',
                'example' => 'Barcelona',
            ],
            'offers.*.check_in' => [
                'description' => 'Check-in date (Y-m-d).',
                'example' => '2026-10-10',
            ],
            'offers.*.check_out' => [
                'description' => 'Check-out date (Y-m-d), must be after check_in.',
                'example' => '2026-10-15',
            ],
            'offers.*.max_guests' => [
                'description' => 'Maximum number of guests.',
                'example' => 4,
            ],
            'offers.*.price' => [
                'description' => 'Price in minor currency units (72500 = 725.00 EUR).',
                'example' => 72500,
            ],
            'offers.*.currency' => [
                'description' => 'ISO 4217 currency code.',
                'example' => 'EUR',
            ],
            'offers.*.available_units' => [
                'description' => 'Number of available units.',
                'example' => 2,
            ],
            'offers.*.expires_at' => [
                'description' => 'When this offer expires (ISO-8601).',
                'example' => '2026-09-10T23:59:59Z',
            ],
        ];
    }

    /**
     * @return ImportOfferCollection
     */
    public function offers(): ImportOfferCollection
    {
        return ImportOfferCollection::fromArray($this->validated('offers'));
    }
}
