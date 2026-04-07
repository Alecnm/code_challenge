<?php

declare(strict_types=1);

namespace App\Exceptions\StateMachine;

use LogicException;

final class NoStatesImplementedException extends LogicException
{
    public function __construct(string $class)
    {
       parent::__construct("{$class} must define a map of \$states to use the HasStateMachine trait");
    }
}