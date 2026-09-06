<?php

declare(strict_types=1);

namespace App\DTO\Import;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, string>
 */
final readonly class ImportPropertyDTO implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $code,
        public string $name,
        public string $city,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: (string)$data['code'],
            name: (string)$data['name'],
            city: (string)$data['city'],
        );
    }

    /** @return array{code: string, name: string, city: string} */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'city' => $this->city,
        ];
    }

    /** @return array{code: string, name: string, city: string} */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
