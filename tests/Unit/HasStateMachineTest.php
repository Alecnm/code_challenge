<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Concerns\HasStateMachine;
use App\Exceptions\StateMachine\NoStatesImplementedException;
use App\Exceptions\StateMachine\InvalidNextStateException;

class HasStateMachineTest extends TestCase
{
    /**
     * Behaviors for the state machine to be tested
     * - If the class did not implement states, do we handle the error gracefully? DONE
     * - It validates correctly the transition to the expected states? DONE
     * - The state actually changes after a valid transition? DONE
     * - It handles correctly the errors/exceptions when transitioning to the wrong state DONE
     * - The trigger works for ModelTransitioning?
     * - The trigger works for ModelTransitioned?
     */


    public function test_the_trait_throws_exception_when_states_not_implement_in_model(): void
    {
        $fakeModel = new class {
            use HasStateMachine;

            protected string $currentState = 'approved';

            public function getCurrentState(): string
            {
                return $this->currentState ?? '';
            }
        };

        $this->expectException(NoStatesImplementedException::class);
        $fakeModel->transitionTo('approved');
    }

    public function test_the_trait_validates_the_transition_to_the_expected_state(): void
    {
        $fakeModel = new class {
            use HasStateMachine;

            protected string $currentState = 'submitted';

            protected static array $states = [
                'draft' => ['submitted'],
                'submitted' => ['approved', 'rejected'],
                'approved' => [],
            ];

            public function getCurrentState(): string
            {
                return $this->currentState ?? '';
            }
        };

        $fakeModel->transitionTo('approved');
        $this->assertEquals('approved', $fakeModel->getCurrentState(), 'It did not transitioned to the right state.');
    }

    public function test_the_trait_throws_exception_when_transitioning_to_an_invalid_state(): void
    {
        $fakeModel = new class {
            use HasStateMachine;

            protected string $currentState = 'submitted';

            protected static array $states = [
                'draft' => ['submitted'],
                'submitted' => ['approved', 'rejected'],
                'approved' => [],
            ];

            public function getCurrentState(): string
            {
                return $this->currentState ?? '';
            }
        };

        $this->expectException(InvalidNextStateException::class);
        $fakeModel->transitionTo('draft');
    }
}
