<?php

namespace Maatwebsite\Excel\Validators;

use Illuminate\Contracts\Support\Arrayable;

class Failure implements Arrayable
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

    /**
     * @var array
     */
    private $values;

    /**
     * @param int    $row
     * @param string $attribute
     * @param array  $errors
     * @param array  $values
     */
    public function __construct(int $row, string $attribute, array $errors, array $values = [])
    {
        $this->row       = $row;
        $this->attribute = $attribute;
        $this->errors    = $errors;
        $this->values    = $values;
    }

    /**
     * @return int
     */
    public function row(): int
    {
        return $this->row;
    }

    /**
     * @return string
     */
    public function attribute(): string
    {
        return $this->attribute;
    }

    /**
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function values(): array
    {
        return $this->values;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return collect($this->errors)->map(function ($message) {
            return __('There was an error on row :row. :message', ['row' => $this->row, 'message' => $message]);
        })->all();
    }
}
