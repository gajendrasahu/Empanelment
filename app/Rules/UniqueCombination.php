<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueCombination implements Rule
{
    private $table;
    private $field1;
    private $field2;

    public function __construct($table, $field1, $field2)
    {
        $this->table = $table;
        $this->field1 = $field1;
        $this->field2 = $field2;
    }

    public function passes($attribute, $value)
    {
        $count = DB::table($this->table)
            ->where($this->field1, $value[$this->field1])
            ->where($this->field2, $value[$this->field2])
            ->count();

        return $count === 0;
    }

    public function message()
    {
        return 'COMBINATION ALREADY EXIST IN DATABASE';
    }
}