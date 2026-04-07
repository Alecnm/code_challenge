<?php

declare(strict_types=1);

namespace App\Exceptions\RuleEvaluator;

use LogicException;

final class FieldNotPresentInUser extends LogicException
{
    public function __construct(readonly string $field)
    {
        return parent::__construct("The field: {$field} does not exist in the user model properties. The rule set might be corrupted");
    }
}