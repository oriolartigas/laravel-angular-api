<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Translation\PotentiallyTranslatedString;

class AllowedWhereFields implements ValidationRule
{
    protected Model $model;

    /**
     * Create a new rule instance.
     *
     * @param  Model  $model  The Model instance used to retrieve the whitelist.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute  The field name ('where').
     * @param  mixed  $value  The field value (the associative array of filters).
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array(value: $value) || empty($value)) {
            return;
        }

        $whereableFields = collect(value: $this->model->getWhereable())
            ->merge(items: $this->model->getMandatoryWhereable())
            ->unique();

        $requestedWhereKeys = collect(value: $value)->keys();
        $invalidFields = $requestedWhereKeys->diff(items: $whereableFields);

        if ($invalidFields->isNotEmpty()) {
            $fail('Invalid filtering field(s) requested for '.$attribute.'. The following were disallowed: '.$invalidFields->implode(value: ', '));
        }
    }
}
