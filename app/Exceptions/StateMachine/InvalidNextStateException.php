<?php

declare(strict_types=1);

namespace App\Exceptions\StateMachine;

use LogicException;

final class InvalidNextStateException extends LogicException
{
    public function __construct(string $state)
    {
        $message = $state ? 
            "{$state} is not valid to transition" : 
            "\$state cannot be empty to transition";
            
       parent::__construct($message);
    }
}