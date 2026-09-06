<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SearchPropertiesRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'city' => ['nullable', 'string', 'max:120'],
            'check_in' => ['required', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get the request's query parameters.
     *
     * @return array<string, mixed>
     */
    public function queryParameters(): array
    {
        return [
            'city' => [
                'description' => 'Filter by city.',
                'example' => 'Barcelona',
            ],
            'check_in' => [
                'description' => 'Check-in date (Y-m-d).',
                'example' => '2026-10-10',
            ],
            'check_out' => [
                'description' => 'Check-out date (Y-m-d), must be after check_in.',
                'example' => '2026-10-15',
            ],
            'guests' => [
                'description' => 'Minimum number of guests the property must accommodate.',
                'example' => 2,
            ],
            'per_page' => [
                'description' => 'Items per page (1-100).',
                'example' => 15,
            ],
        ];
    }
}
