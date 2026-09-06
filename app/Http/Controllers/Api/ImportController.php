<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\Import\ImportAcceptedResource;
use App\Http\Resources\Import\ImportResource;
use App\Models\Import;
use App\Services\ImportService;
use App\Services\SupplierService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Throwable;

#[Group(
    name: 'Imports',
    description: 'Asynchronous import of supplier offers.',
    authenticated: false,
)]
final class ImportController extends Controller
{
    /**
     * @param ImportService $importService
     * @param SupplierService $supplierService
     */
    public function __construct(
        private readonly ImportService $importService,
        private readonly SupplierService $supplierService,
    ) {}

    /**
     * @param StoreImportRequest $request
     *
     * @throws Throwable
     *
     * @return JsonResponse
     */
    #[Endpoint(
        title: 'Register an import',
        description: 'Validates the payload, stores the import and queues it for processing. Returns immediately with 202.',
    )]
    #[ResponseFromApiResource(ImportAcceptedResource::class, Import::class, 202)]
    public function store(StoreImportRequest $request): JsonResponse
    {
        $supplier = $this->supplierService->findByCode($request->validated('supplier'));

        $import = $this->importService->register(
            supplier: $supplier,
            externalImportId: $request->validated('external_import_id'),
            sentAt: CarbonImmutable::parse($request->validated('sent_at')),
            offers: $request->offers(),
        );

        return ImportAcceptedResource::make($import)->response()->setStatusCode(202);
    }

    /**
     * @param Import $import
     *
     * @return ImportResource
     */
    #[Endpoint(title: 'Get import status')]
    #[ResponseFromApiResource(ImportResource::class, Import::class, 200)]
    public function show(Import $import): ImportResource
    {
        $import->load('supplier');

        return ImportResource::make($import);
    }
}
