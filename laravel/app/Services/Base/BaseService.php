<?php

declare(strict_types=1);

namespace App\Services\Base;

use App\Contracts\Base\BaseServiceInterface;
use App\Repositories\Base\BaseRepository;

/**
 * @property BaseRepository $repository
 */
abstract class BaseService implements BaseServiceInterface {}
