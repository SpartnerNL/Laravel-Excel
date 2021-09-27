<?php

namespace Maatwebsite\Excel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ExportMakeCommand extends GeneratorCommand
{
    use WithModelStub;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new export class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Export';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('model') && $this->option('query')) {
            return $this->resolveStubPath('/stubs/export.query-model.stub');
        } elseif ($this->option('model')) {
            return $this->resolveStubPath('/stubs/export.model.stub');
        } elseif ($this->option('query')) {
            return $this->resolveStubPath('/stubs/export.query.stub');
        }

        return $this->resolveStubPath('/stubs/export.plain.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Exports';
    }

    /**
     * Build the class with the given name.
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = [];
        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
        }

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate an export for the given model.'],
            ['query', '', InputOption::VALUE_NONE, 'Generate an export for a query.'],
        ];
    }
}
