<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class AllowedSortFields implements ValidationRule
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
     * @param  string  $attribute  The field name ('sort').
     * @param  mixed  $value  The field value (the comma-separated string of fields).
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $sortableFields = collect(value: $this->model->getSortable());

        $requestedSortFields = collect(value: explode(separator: ',', string: (string) $value))
            ->filter()
            ->unique()
            ->map(callback: function (string $field): string {
                $sign = $field[0] ?? null;

                return ($sign === '-' || $sign === '+') ? substr(string: $field, offset: 1) : $field;
            })
            ->filter();

        $invalidFields = $requestedSortFields->diff($sortableFields);

        if ($invalidFields->isNotEmpty()) {
            $fail('Invalid sorting field(s) requested for '.$attribute.'. The following were disallowed: '.$invalidFields->implode(', '));
        }
    }
}
