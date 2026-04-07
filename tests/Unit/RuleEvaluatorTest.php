<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Exceptions\RuleEvaluator\InvalidOperatorException;
use App\Exceptions\RuleEvaluator\FieldNotPresentInUser;
use App\RuleEvaluator;
class RuleEvaluatorTest extends TestCase
{
   
    /**
     * Behaviors for the rule evaluator to be tested
     * It throws an exception when an invalid operator is provided in the rule set DONE
     * It throws an exception when at least 1 field is not present in the user model (since all users should share same fields) DONE
     * It validates correctly a permission for a user that can perform an action returning true
     * It validates correctly a permission for a user that cannot perform an action returning false
     * It throws an exception when an invalid rule set is provided (incorrect array)
     */

    public function test_the_evaluator_throws_exception_when_invalid_operator_provided(): void
    {
        $fakeUser = new class {
            public function __construct(
                public string $role = 'staff',
                public string $email_verified_at = '2026-04-07 16:53:05',
            ) {}
        };

        $ruleSetJson = <<<JSON
            {
                "action": "submit_form",
                "rules": [
                    {
                        "field": "role",
                        "operator": "===",
                        "value": "staff"
                    },
                    {
                        "field": "email_verified_at",
                        "operator": "!=",
                        "value": null
                    }
                ]
            }
        JSON;

        $ruleSet = json_decode($ruleSetJson, true);
        $this->expectException(InvalidOperatorException::class);
        RuleEvaluator::can($fakeUser, $ruleSet);
    }

     public function test_the_evaluator_throws_exception_when_field_not_found_in_user(): void
    {
        $fakeUser = new class {
            public function __construct(
                public string $role = 'staff',
                public string $email_verified_at = '2026-04-07 16:53:05',
            ) {}
        };

        $ruleSetJson = <<<JSON
            {
                "action": "submit_form",
                "rules": [
                    {
                        "field": "rol",
                        "operator": "==",
                        "value": "staff"
                    },
                    {
                        "field": "email_verified_at",
                        "operator": "!=",
                        "value": null
                    }
                ]
            }
        JSON;

        $ruleSet = json_decode($ruleSetJson, true);
        $this->expectException(FieldNotPresentInUser::class);
        RuleEvaluator::can($fakeUser, $ruleSet);
    }



}
