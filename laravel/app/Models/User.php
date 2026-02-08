<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\BaseModelTrait;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use BaseModelTrait;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    // --- BASIC MODEL ATTRIBUTES  ---

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- CUSTOM MODEL ATTRIBUTES FOR FILTER, SORT, EAGER LOAD  ---

    /**
     * Attributes that can be searched.
     *
     * @var array<string>
     */
    protected $whereable = [
        'name',
        'email',
    ];

    /**
     * Attributes that must be present to search.
     *
     * @var array<string>
     */
    protected $mandatoryWhereable = [];

    /**
     * Attributes that can be sorted.
     *
     * @var array<string>
     */
    protected $sortable = [
        'name',
        'roles_count',
    ];

    /**
     * Relations that can be searched.
     *
     * @var array<string>
     */
    protected $withable = [
        'roles',
        'addresses',
    ];

    /**
     * Relations that can be counted.
     *
     * @var array<string>
     */
    protected $withCountable = [
        'roles',
        'addresses',
    ];

    /**
     * Columns that should NOT be qualified with the table name (withCount, withSum...)
     * to avoid SQL errors.
     *
     * @var array<string>
     */
    protected $aggregates = [
        'roles_count',
        'addresses_count',
    ];

    /**
     * The fields in the request that map to Many-to-Many relations
     * and should be synchronized after the model is created or updated.
     */
    protected array $syncableRelations = [
        'role_ids' => 'roles',
    ];

    /**
     * The fields in the request that map to One-to-Many relations
     * and should be created after the model is created or updated.
     */
    protected array $creatableRelations = [
        'addresses' => 'addresses',
    ];

    // --- RELATIONS  ---

    /**
     * Addresses relation
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(related: Address::class);
    }

    /**
     * Roles relation
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(related: Role::class);
    }
}
