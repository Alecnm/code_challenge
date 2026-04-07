<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Exceptions\StateMachine\NoStatesImplementedException;
use App\Exceptions\StateMachine\InvalidNextStateException;
use App\Events\ModelTransitioned;
use App\Events\ModelTransitioning;

trait HasStateMachine
{
    /**
     * @property-read array<string, string[]> $states Defines allowed state transitions.
     *                                                 Must be defined in the using class.
     *                                                 Example: ['draft' => ['submitted'], 'submitted' => ['approved']]
     */
    public function transitionTo(string $state): void
    {
        if (!isset(static::$states)) {
            throw new NoStatesImplementedException(static::class);
        }
            
        $validStates = static::$states[$this->currentState ?? ''] ?? [];
        
        if (!in_array($state, $validStates)) {
            throw new InvalidNextStateException($state);
        }
                
        ModelTransitioning::dispatch($this);
        $this->currentState = $state;
        ModelTransitioned::dispatch($this);
    }
}