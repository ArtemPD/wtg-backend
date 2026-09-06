<?php

declare(strict_types=1);

namespace App\Casts;

use App\DTO\Import\ImportOfferCollection;
use App\DTO\Import\ImportOfferDTO;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<ImportOfferCollection, ImportOfferCollection>
 */
final class ImportOffersCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ImportOfferCollection
    {
        if ($value === null) {
            return new ImportOfferCollection();
        }

        /** @var array<int, array<string, mixed>> $decoded */
        $decoded = json_decode((string)$value, true);

        return ImportOfferCollection::fromArray($decoded);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (!$value instanceof ImportOfferCollection) {
            throw new InvalidArgumentException(
                sprintf('The [%s] attribute must be an instance of %s.', $key, ImportOfferCollection::class),
            );
        }

        // Collection::toArray() maps through new static(...), which would
        // re-run ImportOfferCollection's constructor type check against the
        // already-converted plain arrays and throw. Serializing element by
        // element avoids that.
        $rows = array_map(
            static fn (ImportOfferDTO $offer): array => $offer->toArray(),
            $value->all(),
        );

        return json_encode($rows, JSON_THROW_ON_ERROR);
    }
}
