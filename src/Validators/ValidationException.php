<?php

namespace Maatwebsite\Excel\Validators;

use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Override;

class ValidationException extends IlluminateValidationException
{
    public function __construct(
        IlluminateValidationException $previous,
        /** @var Failure[] */
        protected array $failures,
    ) {
        parent::__construct($previous->validator, $previous->response, $previous->errorBag);
    }

    /**
     * @return list<list<string>>
     */
    #[Override]
    public function errors(): array
    {
        return collect($this->failures)->map->toArray()->all();
    }

    /**
     * @return array<int, Failure>
     */
    public function failures(): array
    {
        return $this->failures;
    }
}
