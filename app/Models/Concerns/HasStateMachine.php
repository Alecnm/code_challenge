<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Exceptions\StateMachine\NoStatesImplementedException;


trait HasStateMachine
{
    public function transitionTo(string $state): bool
    {
        if (!isset(static::$states)) {
            throw new NoStatesImplementedException(static::class);
        }

        return true;
    }
}