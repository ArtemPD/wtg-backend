<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ImportOffersCast;
use App\Enums\ImportStatus;
use Database\Factories\ImportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Import extends Model
{
    /** @use HasFactory<ImportFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'supplier_id',
        'external_import_id',
        'sent_at',
        'status',
        'total_offers',
        'processed_offers',
        'payload',
        'error',
        'completed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'status' => ImportStatus::class,
            'total_offers' => 'integer',
            'processed_offers' => 'integer',
            'payload' => ImportOffersCast::class,
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return HasMany
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}
