<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Property;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

final class PropertySearchService
{
    /**
     * @param CarbonImmutable $checkIn
     * @param CarbonImmutable $checkOut
     * @param int $guests
     * @param string|null $city
     * @param int $perPage
     * @param int $page
     *
     * @return Paginator
     */
    public function search(CarbonImmutable $checkIn, CarbonImmutable $checkOut, int $guests, ?string $city = null, int $perPage = 15, int $page = 1): Paginator
    {
        $ranked = DB::table('offers')
            ->join('properties', 'properties.id', '=', 'offers.property_id')
            ->select([
                'offers.id',
                'offers.property_id',
                'offers.supplier_id',
                'offers.price',
                'offers.currency',
                'offers.available_units',
                'offers.expires_at',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY offers.property_id ORDER BY offers.price ASC, offers.id ASC) AS rn'),
            ])
            ->where('offers.check_in', $checkIn->toDateString())
            ->where('offers.check_out', $checkOut->toDateString())
            ->where('offers.max_guests', '>=', $guests)
            ->where('offers.available_units', '>', 0)
            ->where('offers.expires_at', '>', now())
            ->when($city !== null, fn(Builder $query): Builder => $query->where('properties.city', $city));

        return Property::query()
            ->joinSub($ranked, 'best', fn(JoinClause $join): JoinClause => $join
                ->on('best.property_id', '=', 'properties.id')
                ->where('best.rn', '=', 1))
            ->join('suppliers', 'suppliers.id', '=', 'best.supplier_id')
            ->select([
                'properties.id', 'properties.code', 'properties.name', 'properties.city',
                'best.id as best_offer_id',
                'best.price as best_offer_price',
                'best.currency as best_offer_currency',
                'best.available_units as best_offer_available_units',
                'best.expires_at as best_offer_expires_at',
                'suppliers.code as best_offer_supplier',
            ])
            ->orderBy('best.price')
            ->orderBy('properties.id')
            ->simplePaginate(perPage: $perPage, page: $page);
    }
}
