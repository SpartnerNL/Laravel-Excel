<?php

namespace Maatwebsite\Excel\Validators;

use Illuminate\Contracts\Validation\Factory;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\ValidationException as IlluminateValidationException;

class RowValidator
{
    /**
     * @var Factory
     */
    private $validator;

    /**
     * @param Factory $validator
     */
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param array          $rows
     * @param WithValidation $import
     *
     * @throws ValidationException
     */
    public function validate(array $rows, WithValidation $import)
    {
        $rules      = $this->rules($import);
        $messages   = $this->messages($import);
        $attributes = $this->attributes($import);

        try {
            $this->validator->make($rows, $rules, $messages, $attributes)->validate();
        } catch (IlluminateValidationException $e) {
            $failures = [];
            foreach ($e->errors() as $attribute => $messages) {
                $row           = strtok($attribute, '.');
                $attributeName = strtok('');
                $attributeName = $attributes['*.' . $attributeName] ?? $attributeName;

                $failures[] = new Failure(
                    $row,
                    $attributeName,
                    str_replace($attribute, $attributeName, $messages)
                );
            }

            if ($import instanceof SkipsOnFailure) {
                $import->onFailure(...$failures);
            } else {
                throw new ValidationException(
                    $e,
                    $failures
                );
            }
        }
    }

    /**
     * @param WithValidation $import
     *
     * @return array
     */
    private function messages(WithValidation $import): array
    {
        return method_exists($import, 'customValidationMessages')
            ? $this->formatKey($import->customValidationMessages())
            : [];
    }

    /**
     * @param WithValidation $import
     *
     * @return array
     */
    private function attributes(WithValidation $import): array
    {
        return method_exists($import, 'customValidationAttributes')
            ? $this->formatKey($import->customValidationAttributes())
            : [];
    }

    /**
     * @param WithValidation $import
     *
     * @return array
     */
    private function rules(WithValidation $import): array
    {
        return $this->formatKey($import->rules());
    }

    /**
     * @param array $elements
     *
     * @return array
     */
    private function formatKey(array $elements): array
    {
        return collect($elements)->mapWithKeys(function ($rule, $attribute) {
            $attribute = starts_with($attribute, '*.') ? $attribute : '*.' . $attribute;

            return [$attribute => $rule];
        })->all();
    }
}
