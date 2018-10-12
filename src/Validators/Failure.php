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
     * @param int    $row
     * @param string $attribute
     * @param array  $errors
     */
    public function __construct(int $row, string $attribute, array $errors)
    {
        $this->row       = $row;
        $this->attribute = $attribute;
        $this->errors    = $errors;
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
    public function toArray()
    {
        return collect($this->errors)->map(function ($message) {
            return __('There was an error on row :row. :message', ['row' => $this->row, 'message' => $message]);
        })->all();
    }
}
