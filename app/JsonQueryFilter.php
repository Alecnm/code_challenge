<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class JsonQueryFilter
{
    public static function apply(Builder $query, array $filters): Builder
    {
        $rootModel = Str::singular($query->getModel()->getTable());
        $firstRelation = true;

        foreach ($filters as $key => $value) {
            [$relation, $column] = explode('.', $key, 2);

            if ($relation === $rootModel) {
                $query->where($column, $value);
            } elseif ($firstRelation) {
                $query->whereHas($relation, fn($q) => $q->where($column, $value));
                $firstRelation = false;
            } else {
                $query->orWhereHas($relation, fn($q) => $q->where($column, $value));
            }
        }

        return $query;
    }
}
