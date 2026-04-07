<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Concerns\HasStateMachine;
use App\Exceptions\StateMachine\NoStatesImplementedException;

class HasStateMachineTest extends TestCase
{
    /**
     * Behaviors for the state machine to be tested
     * - If the class did not implement states, do we handle the error gracefully?
     * - It validates correctly the transition to the expected states?
     * - It handles correctly the errors/exceptions when transitioning to the wrong state
     * - The trigger works for ModelTransitioning?
     * - The trigger works for ModelTransitioned?
     * - The state actually changes after a valid transition?
     */


    public function test_the_trait_throws_exception_when_states_not_implement_in_model(): void
    {
        $fakeModel = new class {
            use HasStateMachine;

            protected string $state;

            public function save(): bool
            {
                /**
                 * Do some stuff, then transitions to a state
                 */
                $newState = "approved";
                return $this->transitionTo($newState);
            }

            public function getState(): string
            {
                return $this->state ?? '';
            }
        };

        $this->expectException(NoStatesImplementedException::class);
        $result = $fakeModel->save();

        
    }
   
}
