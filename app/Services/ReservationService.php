<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DuplicateReservationException;
use App\Exceptions\OfferExpiredException;
use App\Exceptions\OfferSoldOutException;
use App\Models\Offer;
use App\Models\Reservation;
use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ReservationService
{
    /**
     * @param Offer $offer
     * @param string $clientReference
     * @param string $customerName
     * @param string $customerEmail
     *
     * @throws DuplicateReservationException
     * @throws OfferExpiredException
     * @throws OfferSoldOutException
     * @throws Throwable
     *
     * @return Reservation
     */
    public function reserve(Offer $offer, string $clientReference, string $customerName, string $customerEmail): Reservation
    {
        DB::beginTransaction();

        try {
            $lockedOffer = Offer::query()->whereKey($offer->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedOffer->expires_at <= now()) {
                throw new OfferExpiredException($lockedOffer);
            }

            if ($lockedOffer->available_units <= 0) {
                throw new OfferSoldOutException($lockedOffer);
            }

            $lockedOffer->decrement('available_units');

            $reservation = Reservation::query()->create([
                'offer_id' => $lockedOffer->id,
                'client_reference' => $clientReference,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'price' => $lockedOffer->price,
                'currency' => $lockedOffer->currency,
                'check_in' => $lockedOffer->check_in,
                'check_out' => $lockedOffer->check_out,
            ]);

            DB::commit();

            return $reservation;
        } catch (UniqueConstraintViolationException) {
            DB::rollBack();

            throw new DuplicateReservationException($clientReference);
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }
}
