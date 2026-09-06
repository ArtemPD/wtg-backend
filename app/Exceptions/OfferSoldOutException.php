<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Offer;
use Exception;

final class OfferSoldOutException extends Exception
{
    public function __construct(public readonly Offer $offer)
    {
        parent::__construct('Offer is sold out.');
    }
}
