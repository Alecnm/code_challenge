<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Exceptions\StateMachine\NoStatesImplementedException;
use App\Exceptions\StateMachine\InvalidNextStateException;


trait HasStateMachine
{
    public function transitionTo(string $state): void
    {
        if (!isset(static::$states)) {
            throw new NoStatesImplementedException(static::class);
        }

        $validStates = static::$states[$this->currentState ?? ''] ?? [];

        if (!in_array($state, $validStates)) {
            throw new InvalidNextStateException($state);
        }

        $this->currentState = $state;
    }
}