<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\ValidationRule;

class Unique implements ValidationRule
{
    public function __construct(private string $model)
    {
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $model = App::make($this->model);

        $attr = Str::afterLast($attribute, '.');

        $exists = $model->firstWhere($attr, $value);

        if ($exists) {
            $fail("{$value} already exists.");
        }
    }
}
