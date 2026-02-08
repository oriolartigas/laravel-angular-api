<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class AllowedQueryRelations implements ValidationRule
{
    protected Model $model;

    protected string $methodName;

    public function __construct(Model $model, string $methodName = 'getWithable')
    {
        $this->model = $model;
        $this->methodName = $methodName;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value) || ! is_string($value)) {
            $fail('The :attribute format is invalid. It must be a comma-separated string.');

            return;
        }

        $allowedRelations = collect($this->model->{$this->methodName}());

        $requestedRelations = collect(explode(',', (string) $value))
            ->filter()
            ->unique();

        $invalidRelations = $requestedRelations->diff($allowedRelations);

        if ($invalidRelations->isNotEmpty()) {
            $fail('Invalid relation(s) requested for :attribute: '.$invalidRelations->implode(', '));
        }
    }
}
