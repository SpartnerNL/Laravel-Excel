<?php

namespace Maatwebsite\Excel\Validators;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Failure implements Arrayable, JsonSerializable
{
    /**
     * @var int
     */
    protected $row;

    /**
     * @var string
     */
    protected $attribute;

    /**
     * @var array
     */
    protected $errors;

    public function __construct(int $row, string $attribute, array $errors, private array $values = [])
    {
        $this->row       = $row;
        $this->attribute = $attribute;
        $this->errors    = $errors;
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

    /**
     * @return array
     */
    public function toArray()
    {
        return collect($this->errors)->map(fn ($message) => __('There was an error on row :row. :message', ['row' => $this->row, 'message' => $message]))->all();
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
