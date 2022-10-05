<?php

namespace App\v2\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SortByNameScope implements Scope {

    private string $column;
    private $direction;

    public function __construct($column, $direction = 'asc') {
        $this->column = $column;
        $this->direction = $direction;
    }

    public function apply(Builder $builder, Model $model) {
        $builder->orderBy($this->column, $this->direction);
    }
}
