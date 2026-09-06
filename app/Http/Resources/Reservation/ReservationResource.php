<?php

declare(strict_types=1);

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\ResponseField;

final class ReservationResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    #[ResponseField('id', type: 'integer', description: 'Reservation ID')]
    #[ResponseField('offer_id', type: 'integer', description: 'Reserved offer ID')]
    #[ResponseField('client_reference', type: 'string', description: 'Client-supplied idempotency reference')]
    #[ResponseField('customer_name', type: 'string', description: 'Customer name')]
    #[ResponseField('customer_email', type: 'string', description: 'Customer email')]
    #[ResponseField('price', type: 'integer', description: 'Price snapshot in minor currency units')]
    #[ResponseField('currency', type: 'string', description: 'Currency snapshot')]
    #[ResponseField('created_at', type: 'string', description: 'When this reservation was created (ISO-8601 UTC)')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'offer_id' => $this->offer_id,
            'client_reference' => $this->client_reference,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'price' => $this->price,
            'currency' => $this->currency,
            'created_at' => $this->created_at,
        ];
    }
}
