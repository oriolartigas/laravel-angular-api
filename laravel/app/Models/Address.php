<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use BaseModelTrait;

    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    // --- BASIC MODEL ATTRIBUTES  ---

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [];

    // --- CUSTOM MODEL ATTRIBUTES FOR FILTER, SORT, EAGER LOAD  ---

    /**
     * Attributes that can be searched.
     *
     * @var array<string>
     */
    protected $whereable = [
        'user_id',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    /**
     * Attributes that can be sorted.
     *
     * @var array<string>
     */
    protected $sortable = [
        'name',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    /**
     * Relations that can be searched.
     *
     * @var array<string>
     */
    protected $withable = [
        'user',
    ];

    // --- RELATIONS  ---

    /**
     * User relation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }
}
