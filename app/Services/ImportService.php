<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Import\ImportOfferCollection;
use App\Enums\ImportStatus;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ImportService
{
    /**
     * @param SupplierService $supplierService
     */
    public function __construct(private SupplierService $supplierService) {}

    /**
     * @param string $supplierCode
     * @param string $externalImportId
     * @param CarbonImmutable $sentAt
     * @param ImportOfferCollection $offers
     *
     * @throws Throwable
     *
     * @return Import
     */
    public function register(string $supplierCode, string $externalImportId, CarbonImmutable $sentAt, ImportOfferCollection $offers): Import
    {
        $supplier = $this->supplierService->findByCode($supplierCode);

        DB::beginTransaction();

        try {
            $import = Import::query()->firstOrCreate(
                [
                    'supplier_id' => $supplier->id,
                    'external_import_id' => $externalImportId,
                ],
                [
                    'sent_at' => $sentAt,
                    'status' => ImportStatus::PENDING,
                    'total_offers' => $offers->count(),
                    'payload' => $offers,
                ],
            );

            DB::commit();

            if ($import->wasRecentlyCreated) {
                ProcessImportJob::dispatch($import);
            }

            return $import;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }
}
