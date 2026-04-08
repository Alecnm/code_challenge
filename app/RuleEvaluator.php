<?php

declare(strict_types=1);

namespace App;
use App\Exceptions\RuleEvaluator\InvalidOperatorException;
use App\Exceptions\RuleEvaluator\FieldNotPresentInUser;
use App\Exceptions\RuleEvaluator\InvalidRuleSetException;
use Closure;
use \Illuminate\Database\Eloquent\Model;
use ReflectionClass;

final class RuleEvaluator
{
    
    public static function can(object $user, array $ruleSet): bool
    {
        self::validateSchema($ruleSet);

        $modelInfo = new ReflectionClass($user::class);

        foreach($ruleSet['rules'] as $rule) {

            $validator = self::validationEngine($rule['operator']);

            if (!$modelInfo->hasProperty($rule['field']) && 
                !($user instanceof Model && array_key_exists($rule['field'], $user->getAttributes()))) {
                throw new FieldNotPresentInUser($rule['field']);
            }

            if(!$validator($user->{$rule['field']}, $rule['value'])) {
                return false;
            }
        }

        return true;
    }

    private static function validateSchema(array $ruleSet): void
    {
        if (empty($ruleSet['action']) || !is_string($ruleSet['action'])) {
            throw new InvalidRuleSetException('missing or invalid "action" key');
        }

        if (empty($ruleSet['rules']) || !is_array($ruleSet['rules'])) {
            throw new InvalidRuleSetException('missing or invalid "rules" key');
        }

        foreach ($ruleSet['rules'] as $rule) {
            if (!array_key_exists('field', $rule) || !array_key_exists('operator', $rule) || !array_key_exists('value', $rule)) {
                throw new InvalidRuleSetException('each rule must have "field", "operator", and "value" keys');
            }
        }
    }

    private static function validationEngine(string $operator): Closure
    {
        return match ($operator) {
            '==' => fn($mv, $v) => $mv == $v,
            '!=' => fn($mv, $v) => $mv != $v,
            'in' => fn($mv, $v) => in_array($mv, $v),
            'not_in' => fn($mv, $v) => !in_array($mv, $v),
            '>' => fn($mv, $v) => $mv > $v,
            '<' => fn($mv, $v) => $mv < $v,
            'contains' => fn($mv, $v) => self::contains($mv, $v),
            default => throw new InvalidOperatorException($operator),
        };
    }

    private static function contains(string|array $modelValue, string $value): bool
    {
        if (is_array($modelValue)) {
            return in_array($value, $modelValue);
        }

        if (is_string($modelValue)) {
            return str_contains($modelValue, $value);
        }

        return false;
    }
}