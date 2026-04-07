<?php

namespace Tests\Feature;

use App\Events\ModelTransitioned;
use App\Events\ModelTransitioning;
use Tests\TestCase;
use App\Models\Concerns\HasStateMachine;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
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
     * - The trigger works for ModelTransitioning? DONE
     * - The trigger works for ModelTransitioned? DONE
     */


    public function test_the_trait_throws_exception_when_states_not_implement_in_model(): void
    {
        $fakeModel = new class extends Model {
            use HasStateMachine;
            public string $currentState = 'approved';
        };

        $this->expectException(NoStatesImplementedException::class);
        $fakeModel->transitionTo('approved');
    }

    public function test_the_trait_validates_the_transition_to_the_expected_state(): void
    {
        $fakeModel = new class extends Model {
            use HasStateMachine;

            public string $currentState = 'submitted';

            protected static array $states = [
                'draft' => ['submitted'],
                'submitted' => ['approved', 'rejected'],
                'approved' => [],
            ];
        };

        $fakeModel->transitionTo('approved');
        $this->assertEquals('approved', $fakeModel->currentState, 'It did not transitioned to the right state.');
    }

    public function test_the_trait_throws_exception_when_transitioning_to_an_invalid_state(): void
    {
        $fakeModel = new class extends Model {
            use HasStateMachine;

            public string $currentState = 'submitted';

            protected static array $states = [
                'draft' => ['submitted'],
                'submitted' => ['approved', 'rejected'],
                'approved' => [],
            ];
        };

        $this->expectException(InvalidNextStateException::class);
        $fakeModel->transitionTo('draft');
    }

    public function test_the_trait_triggers_the_model_transitioning_event(): void
    {
        $fakeModel = new class extends Model {
            use HasStateMachine;

            public string $currentState = 'submitted';

            protected static array $states = [
                'draft' => ['submitted'],
                'submitted' => ['approved', 'rejected'],
                'approved' => [],
            ];
        };

        Event::fake(ModelTransitioning::class);
        $fakeModel->transitionTo('approved');
        Event::assertDispatched(ModelTransitioning::class);
    }

    public function test_the_trait_triggers_the_model_transitioned_event(): void
    {
        $fakeModel = new class extends Model {
            use HasStateMachine;

            public string $currentState = 'submitted';

            protected static array $states = [
                'draft' => ['submitted'],
                'submitted' => ['approved', 'rejected'],
                'approved' => [],
            ];
        };

        Event::fake(ModelTransitioned::class);
        $fakeModel->transitionTo('approved');
        Event::assertDispatched(ModelTransitioned::class);
    }
}
