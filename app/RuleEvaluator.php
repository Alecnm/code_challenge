<?php

declare(strict_types=1);

namespace App;
use App\Exceptions\RuleEvaluator\InvalidOperatorException;
use App\Exceptions\RuleEvaluator\FieldNotPresentInUser;
use \Illuminate\Database\Eloquent\Model;
use ReflectionClass;

final class RuleEvaluator
{
    public static $validOperators = [
        '==',
        '!=',
        'not_in',
        '>',
        '<',
        'contains'
    ];


    public static function can(object $user, array $ruleSet): bool
    {
        $modelInfo = new ReflectionClass($user::class);

        foreach($ruleSet['rules'] as $rule) {
            if (!in_array($rule['operator'], self::$validOperators)) {
                throw new InvalidOperatorException($rule['operator']);
            }

            if (!$modelInfo->hasProperty($rule['field']) && 
                !($user instanceof Model && array_key_exists($rule['field'], $user->getAttributes()))) {
                throw new FieldNotPresentInUser($rule['field']);
            }

            
        }
        return true;
    }
}