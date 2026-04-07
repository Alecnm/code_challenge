<?php

declare(strict_types=1);

namespace App\Exceptions\RuleEvaluator;

use LogicException;

final class InvalidOperatorException extends LogicException
{
    public function __construct(readonly string $operator)
    {
        return parent::__construct("The operator: {$operator} is not supported. Check the rule set provided");
    }
}