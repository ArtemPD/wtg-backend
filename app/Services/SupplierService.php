<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Supplier;

final class SupplierService
{
    /**
     * @param string $code
     *
     * @return Supplier
     */
    public function findByCode(string $code): Supplier
    {
        return Supplier::query()->where('code', $code)->firstOrFail();
    }
}
