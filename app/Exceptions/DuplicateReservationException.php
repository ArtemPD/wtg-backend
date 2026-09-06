<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class DuplicateReservationException extends Exception
{
    public function __construct(public readonly string $clientReference)
    {
        parent::__construct('Reservation already exists for this client reference.');
    }
}
