<?php

declare(strict_types=1);

namespace App\Http\Resources\Import;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\ResponseField;

final class ImportResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    #[ResponseField('id', type: 'integer', description: 'Import ID')]
    #[ResponseField('supplier', type: 'string', description: 'Supplier code')]
    #[ResponseField('external_import_id', type: 'string', description: 'Import identifier assigned by the supplier')]
    #[ResponseField('sent_at', type: 'string', description: 'When the supplier generated this import (ISO-8601 UTC)')]
    #[ResponseField('status', type: 'string', description: 'Import status')]
    #[ResponseField('total_offers', type: 'integer', description: 'Total number of offers in this import')]
    #[ResponseField('processed_offers', type: 'integer', description: 'Number of offers processed so far')]
    #[ResponseField('error', type: 'string', description: 'Error message if the import failed', nullable: true)]
    #[ResponseField('created_at', type: 'string', description: 'When this import was registered (ISO-8601 UTC)')]
    #[ResponseField('completed_at', type: 'string', description: 'When this import finished processing (ISO-8601 UTC)', nullable: true)]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier' => $this->supplier->code,
            'external_import_id' => $this->external_import_id,
            'sent_at' => $this->sent_at,
            'status' => $this->status->value,
            'total_offers' => $this->total_offers,
            'processed_offers' => $this->processed_offers,
            'error' => $this->error,
            'created_at' => $this->created_at,
            'completed_at' => $this->completed_at,
        ];
    }
}
