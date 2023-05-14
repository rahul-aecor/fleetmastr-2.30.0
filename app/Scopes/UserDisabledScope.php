<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UserDisabledScope implements ScopeInterface
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('is_disabled', 0);
        //$this->extend($builder);
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        $column = 'is_disabled';

        $query = $builder->getQuery();

        foreach ((array) $query->wheres as $key => $where)
        {
            if ($where['column'] == $column)
            {
                unset($query->wheres[$key]);

                $query->wheres = array_values($query->wheres);
            }
        }
        $builder->setBindings([]);
    }
}
?>