<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'offer_id',
        'client_reference',
        'customer_name',
        'customer_email',
        'price',
        'currency',
        'check_in',
        'check_out',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'check_in' => 'date',
            'check_out' => 'date',
        ];
    }

    /**
     * @return BelongsTo
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
