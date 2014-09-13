<?php namespace Maatwebsite\Excel\Files;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

abstract class File {

    /**
     * Get the handler class name
     * @throws LaravelExcelException
     * @return string
     */
    protected function getHandlerClassName($type)
    {
        // Translate the file into a FileHandler
        $class = get_class($this);
        $handler = substr_replace($class, $type . 'Handler', strrpos($class, $type));

        // Check if the handler exists
        if (!class_exists($handler))
            throw new LaravelExcelException("$type handler [$handler] does not exist.");

        return $handler;
    }
}