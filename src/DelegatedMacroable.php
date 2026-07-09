<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Traits\Macroable;

trait DelegatedMacroable
{
    use Macroable {
        __call as __callMacro;
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  array<int, mixed>  $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (method_exists($this->getDelegate(), $method)) {
            return call_user_func_array([$this->getDelegate(), $method], $parameters);
        }

        array_unshift($parameters, $this);

        return $this->__callMacro($method, $parameters);
    }

    abstract public function getDelegate(): object;
}
