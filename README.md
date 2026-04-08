# PHP / Laravel Coding Challenges

Three self-contained Laravel implementations covering a dynamic rule engine, a flexible Eloquent filter, and a lightweight state machine trait. All tests run against an in-memory SQLite database — no external services required.

---

## Challenge Map

### 1. Dynamic Rule Engine

Evaluates whether a user object satisfies a JSON-defined rule set.

| Layer | File |
|---|---|
| Core logic | `app/RuleEvaluator.php` |
| Exceptions | `app/Exceptions/RuleEvaluator/` |
| Tests | `tests/Unit/RuleEvaluatorTest.php` |

`RuleEvaluator::can(object $user, array $ruleSet): bool` accepts any object and a rule set array. Supported operators: `==`, `!=`, `>`, `<`, `in`, `not_in`, `contains`.

---

### 2. Nested Eloquent Search Filter

Applies a flat dot-notation filter payload across Eloquent relationships.

| Layer | File |
|---|---|
| Core logic | `app/JsonQueryFilter.php` |
| Tests | `tests/Feature/JsonQueryFilterTest.php` |

`JsonQueryFilter::apply(Builder $query, array $filters): Builder` maps `relation.column` keys to `whereHas` / `orWhereHas` calls, and falls back to a plain `where` when the key matches the root model table name.

---

### 3. State Machine Trait

A `HasStateMachine` trait that enforces allowed transitions on any Eloquent model.

| Layer | File |
|---|---|
| Trait | `app/Models/Concerns/HasStateMachine.php` |
| Events | `app/Events/ModelTransitioning.php`, `app/Events/ModelTransitioned.php` |
| Exceptions | `app/Exceptions/StateMachine/` |
| Tests | `tests/Feature/HasStateMachineTest.php` |

Models declare a static `$states` map; calling `transitionTo(string $state)` validates the transition and dispatches the before/after events.

---

## Running the Tests

### Locally (PHP 8.3 + Composer required)

```bash
composer install
php artisan test
# or directly via PHPUnit:
vendor/bin/phpunit
```

### With Docker

```bash
# Build and start the container (first time)
docker compose up -d --build

# Run all tests
docker compose exec app php artisan test

# Tear down when done
docker compose down
```

> The SQLite in-memory driver is pre-configured in `phpunit.xml`, so no database setup is needed.
