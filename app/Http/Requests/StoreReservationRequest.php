<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreReservationRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'client_reference' => ['required', 'string', 'max:190'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
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
            'client_reference' => [
                'description' => 'Client-supplied idempotency reference. A duplicate returns 409.',
                'example' => 'web-order-9f782b1c',
            ],
            'customer_name' => [
                'description' => 'Customer name.',
                'example' => 'John Smith',
            ],
            'customer_email' => [
                'description' => 'Customer email.',
                'example' => 'john@example.com',
            ],
        ];
    }
}
