<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class JsonQueryFilter
{
    public static function apply(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            [$relation, $column] = explode('.', $key, 2);
            $rootModel = Str::singular($query->getModel()->getTable());

            if ($relation === $rootModel) {
                $query->where($column, $value);
            }
        }

        return $query;
    }
}
