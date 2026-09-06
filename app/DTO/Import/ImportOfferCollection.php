<?php

declare(strict_types=1);

namespace App\DTO\Import;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * @extends Collection<int, ImportOfferDTO>
 */
final class ImportOfferCollection extends Collection
{
    /** @param iterable<int, ImportOfferDTO> $items */
    public function __construct($items = [])
    {
        foreach ($items as $item) {
            if (!$item instanceof ImportOfferDTO) {
                throw new InvalidArgumentException(
                    sprintf('%s accepts only %s.', self::class, ImportOfferDTO::class),
                );
            }
        }

        parent::__construct($items);
    }

    /** @param array<int, array<string, mixed>> $offers */
    public static function fromArray(array $offers): self
    {
        return new self(array_map(
            static fn (array $offer): ImportOfferDTO => ImportOfferDTO::fromArray($offer),
            $offers,
        ));
    }
}
