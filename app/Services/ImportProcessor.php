<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Import\ImportOfferDTO;
use App\DTO\Import\ImportPropertyDTO;
use App\Enums\ImportStatus;
use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class ImportProcessor
{
    private int $chunkSize = 500;

    /**
     * @param Import $import
     *
     * @throws Throwable
     *
     * @return void
     */
    public function process(Import $import): void
    {
        $import->update(['status' => ImportStatus::PROCESSING]);

        try {
            collect($import->payload)->chunk($this->chunkSize)->each(
                function (Collection $chunk) use ($import): void {
                    $this->processChunk($import, $chunk);
                },
            );

            $import->update([
                'status' => ImportStatus::COMPLETED,
                'completed_at' => now(),
                'error' => null,
            ]);
        } catch (Throwable $exception) {
            $import->update([
                'status' => ImportStatus::FAILED,
                'error' => Str::limit($exception->getMessage(), 500),
            ]);

            throw $exception;
        }
    }

    /**
     * @param Import $import
     * @param Collection $chunk
     *
     * @throws Throwable
     *
     * @return void
     */
    private function processChunk(Import $import, Collection $chunk): void
    {
        DB::beginTransaction();

        try {
            $rows = $chunk
                ->map(fn (ImportOfferDTO $offer): array => $this->syncOffer($import, $offer)->getAttributes())
                ->all();

            Offer::query()->upsert(
                $rows,
                ['supplier_id', 'external_id'],
                [
                    'property_id', 'import_id', 'check_in', 'check_out', 'max_guests',
                    'price', 'currency', 'available_units', 'expires_at', 'imported_at',
                ],
            );

            $import->increment('processed_offers', $chunk->count());

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    private function resolveProperty(ImportPropertyDTO $property): Property
    {
        return Property::query()->updateOrCreate(
            ['code' => $property->code],
            ['name' => $property->name, 'city' => $property->city],
        );
    }

    private function syncOffer(Import $import, ImportOfferDTO $offer): Offer
    {
        $property = $this->resolveProperty($offer->property);

        $row = new Offer([
            'supplier_id' => $import->supplier_id,
            'property_id' => $property->id,
            'import_id' => $import->id,
            'external_id' => $offer->externalId,
            'check_in' => $offer->checkIn,
            'check_out' => $offer->checkOut,
            'max_guests' => $offer->maxGuests,
            'price' => $offer->price,
            'currency' => $offer->currency,
            'available_units' => $offer->availableUnits,
            'expires_at' => $offer->expiresAt,
            'imported_at' => now(),
        ]);

        $row->created_at = now();
        $row->updated_at = now();

        return $row;
    }
}
