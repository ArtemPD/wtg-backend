<?php

declare(strict_types=1);

namespace App\Http\Resources\Property;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\ResponseField;

final class PropertyResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    #[ResponseField('code', type: 'string', description: 'Canonical property code')]
    #[ResponseField('name', type: 'string', description: 'Property name')]
    #[ResponseField('city', type: 'string', description: 'City the property is located in')]
    #[ResponseField('best_offer', type: 'object', description: 'Cheapest actual offer for the requested dates')]
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'city' => $this->city,
            'best_offer' => [
                'id' => (int)$this->best_offer_id,
                'supplier' => $this->best_offer_supplier,
                'price' => (int)$this->best_offer_price,
                'currency' => $this->best_offer_currency,
                'available_units' => (int)$this->best_offer_available_units,
                'expires_at' => CarbonImmutable::parse($this->best_offer_expires_at),
            ],
        ];
    }
}
