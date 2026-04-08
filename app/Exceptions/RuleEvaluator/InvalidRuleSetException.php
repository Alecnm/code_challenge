<?php

declare(strict_types=1);

namespace App\Exceptions\RuleEvaluator;

use LogicException;

final class InvalidRuleSetException extends LogicException
{
    public function __construct(readonly string $reason)
    {
        return parent::__construct("The rule set provided is invalid: {$reason}");
    }
}
