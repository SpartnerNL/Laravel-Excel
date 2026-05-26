<?php

namespace Maatwebsite\Excel\Validators;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Failure implements Arrayable, JsonSerializable
{
    public function __construct(
        protected int $row,
        protected string $attribute,
        protected array $errors,
        private readonly array $values = [],
    ) {
    }

    public function row(): int
    {
        return $this->row;
    }

    public function attribute(): string
    {
        return $this->attribute;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function values(): array
    {
        return $this->values;
    }

    public function toArray(): array
    {
        return collect($this->errors)->map(fn ($message): array|string|null => __('There was an error on row :row. :message', ['row' => $this->row, 'message' => $message]))->all();
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'row'       => $this->row(),
            'attribute' => $this->attribute(),
            'errors'    => $this->errors(),
            'values'    => $this->values(),
        ];
    }
}
