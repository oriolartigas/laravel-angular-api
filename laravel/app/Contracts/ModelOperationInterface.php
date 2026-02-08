<?php

namespace App\Contracts;

use Throwable;

interface ModelOperationInterface extends Throwable
{
    /**
     * Operation type (create, update...)
     */
    public function getOperation(): string;

    /**
     * Default message of the operation
     */
    public function getDefaultMessage(): string;

    /**
     * Model name of the operation
     */
    public function getModelName(): string;
}
