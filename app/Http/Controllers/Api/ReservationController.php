<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\DuplicateReservationException;
use App\Exceptions\OfferExpiredException;
use App\Exceptions\OfferSoldOutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\Reservation\ReservationResource;
use App\Models\Offer;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Throwable;

#[Group(
    name: 'Reservations',
    description: 'Safely reserve an offer.',
    authenticated: false,
)]
final class ReservationController extends Controller
{
    /**
     * @param ReservationService $reservationService
     */
    public function __construct(private readonly ReservationService $reservationService) {}

    /**
     * @param Offer $offer
     * @param StoreReservationRequest $request
     *
     * @throws Throwable
     * @throws DuplicateReservationException
     * @throws OfferExpiredException
     * @throws OfferSoldOutException
     *
     * @return JsonResponse
     */
    #[Endpoint(
        title: 'Reserve an offer',
        description: 'Decrements available units inside a locked transaction.',
    )]
    #[ResponseFromApiResource(ReservationResource::class, Reservation::class, 201)]
    public function store(Offer $offer, StoreReservationRequest $request): JsonResponse
    {
        $reservation = $this->reservationService->reserve(
            offer: $offer,
            clientReference: $request->validated('client_reference'),
            customerName: $request->validated('customer_name'),
            customerEmail: $request->validated('customer_email'),
        );

        return ReservationResource::make($reservation)->response()->setStatusCode(201);
    }
}
