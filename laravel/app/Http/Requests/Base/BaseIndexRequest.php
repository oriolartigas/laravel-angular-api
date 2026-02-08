<?php

declare(strict_types=1);

namespace App\Http\Requests\Base;

use App\Rules\AllowedQueryRelations;
use App\Rules\AllowedSortFields;
use App\Rules\AllowedWhereFields;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class BaseIndexRequest extends FormRequest
{
    /**
     * The service class associated with the request.
     */
    abstract public function getServiceClass(): string;

    abstract public function getModelClass(): string;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Check if the model has mandatory fields
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Check for mandatory whereable fields
            $this->validateMandatoryFields($validator);
        });
    }

    /**
     * Ensures all mandatory fields for filtering are present in the 'where' parameter.
     */
    protected function validateMandatoryFields(Validator $validator): void
    {
        $modelClass = $this->getModelClass();
        $model = new $modelClass;

        $whereParams = collect(value: $this->get(key: 'where', default: []));
        $requestKeys = $whereParams->keys();

        $mandatoryFields = collect(value: $model->getMandatoryWhereable());

        if ($mandatoryFields->isNotEmpty()) {
            $diff = $mandatoryFields->diff(items: $requestKeys);

            if ($diff->isNotEmpty()) {
                $message = app()->environment(['development'])
                    ? 'There are mandatory fields missing for model '.class_basename(class: $model).': '.$diff->implode(value: ', ')
                    : 'There are mandatory fields missing.';

                $validator->errors()->add(key: 'mandatory_fields', message: $message);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $modelClass = $this->getModelClass();
        $model = new $modelClass;
        $hasMandatoryFields = count(value: $model->getMandatoryWhereable()) > 0;

        $whereRules = [
            'nullable',
            'array',
            new AllowedWhereFields(model: $model),
        ];

        if (! $hasMandatoryFields) {
            $whereRules[] = 'min:1';
        }

        return [
            'where' => $whereRules,
            'where.*' => 'nullable|string|max:255',
            'with' => [
                'nullable',
                'string',
                'max:255',
                new AllowedQueryRelations(model: $model, methodName: 'getWithable'),
            ],
            'withCount' => [
                'nullable',
                'string',
                'max:255',
                new AllowedQueryRelations(model: $model, methodName: 'getWithCountable'),
            ],
            'sort' => [
                'nullable',
                'string',
                new AllowedSortFields(model: $model),
            ],
        ];
    }
}
