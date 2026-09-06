<?php

declare(strict_types=1);

namespace App\Http\Resources\Import;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\ResponseField;

final class ImportAcceptedResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[ResponseField('id', type: 'integer', description: 'Import ID')]
    #[ResponseField('status', type: 'string', description: 'Import status')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
        ];
    }
}
