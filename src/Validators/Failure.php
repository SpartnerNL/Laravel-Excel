<?php

namespace Maatwebsite\Excel\Validators;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<array-key, mixed>
 */
class Failure implements Arrayable, JsonSerializable
{
    /**
     * @param  list<string>  $errors
     * @param  array<array-key, mixed>  $values
     */
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

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function values(): array
    {
        return $this->values;
    }

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return collect($this->errors)->map(fn ($message): array|string => __('There was an error on row :row. :message', ['row' => $this->row, 'message' => $message]))->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'row'       => $this->row(),
            'attribute' => $this->attribute(),
            'errors'    => $this->errors(),
            'values'    => $this->values(),
        ];
    }
}
