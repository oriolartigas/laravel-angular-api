<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BaseModelTrait;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use BaseModelTrait;

    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Attributes that can be searched.
     *
     * @var array<string>
     */
    protected array $whereable = ['name'];

    /**
     * Attributes that can be sorted.
     *
     * @var array<string>
     */
    protected array $sortable = ['name'];

    /**
     * Attributes that must be present to search.
     *
     * @var array<string>
     */
    protected array $withable = ['users'];

    /**
     * Relations that can be counted.
     *
     * @var array<string>
     */
    protected array $withCountable = ['users'];

    /**
     * The fields in the request that map to Many-to-Many relations
     * and should be synchronized after the model is created or updated.
     */
    protected array $syncableRelations = [
        'user_ids' => 'users',
    ];

    /**
     * Users relation
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class);
    }
}
