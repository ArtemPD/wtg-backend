<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchPropertiesRequest;
use App\Http\Resources\Property\PropertyResource;
use App\Models\Property;
use App\Services\PropertySearchService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group(
    name: 'Properties',
    description: 'Search for the cheapest actual offer.',
    authenticated: false,
)]
final class PropertyController extends Controller
{
    /**
     * @param PropertySearchService $propertySearchService
     */
    public function __construct(private readonly PropertySearchService $propertySearchService) {}

    /**
     * @param SearchPropertiesRequest $request
     *
     * @return AnonymousResourceCollection
     */
    #[Endpoint(
        title: 'Search properties with their cheapest offer',
        description: 'Returns properties that have at least one actual offer for the given dates, ordered by price.',
    )]
    #[ResponseFromApiResource(
        PropertyResource::class,
        Property::class,
        collection: true,
        simplePaginate: 15,
    )]
    public function index(SearchPropertiesRequest $request): AnonymousResourceCollection
    {
        $properties = $this->propertySearchService->search(
            checkIn: CarbonImmutable::parse($request->validated('check_in')),
            checkOut: CarbonImmutable::parse($request->validated('check_out')),
            guests: (int)$request->validated('guests'),
            city: $request->validated('city'),
            perPage: (int)($request->validated('per_page') ?? 15),
        );

        return PropertyResource::collection($properties);
    }
}
