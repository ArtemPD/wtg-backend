<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\ImportAcceptedResource;
use App\Models\Import;
use App\Services\ImportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
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
     */
    public function __construct(private readonly ImportService $importService) {}

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
    #[Response(status: 422, description: 'Validation failed or unknown supplier code.')]
    public function store(StoreImportRequest $request): JsonResponse
    {
        $import = $this->importService->register(
            supplierCode: $request->validated('supplier'),
            externalImportId: $request->validated('external_import_id'),
            sentAt: CarbonImmutable::parse($request->validated('sent_at')),
            offers: $request->offers(),
        );

        return ImportAcceptedResource::make($import)->response()->setStatusCode(202);
    }
}
