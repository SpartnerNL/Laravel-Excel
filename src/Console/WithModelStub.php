<?php

namespace Maatwebsite\Excel\Console;

use Illuminate\Support\Str;
use InvalidArgumentException;

trait WithModelStub
{
    /**
     * Build the model replacement values.
     *
     * @param  array $replace
     *
     * @return array
     */
    protected function buildModelReplacements(array $replace): array
    {
        $modelClass = $this->parseModel($this->option('model'));

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            'DummyModelClass'     => class_basename($modelClass),
        ]);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string $model
     *
     * @return string
     */
    protected function parseModel($model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (!Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace())) {
            $model = $rootNamespace . $model;
        }

        return $model;
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }
}
