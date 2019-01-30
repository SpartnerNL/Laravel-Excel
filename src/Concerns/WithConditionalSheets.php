<?php

namespace Maatwebsite\Excel\Concerns;

trait WithConditionalSheets
{
    /**
     * @var array
     */
    protected $conditionallySelectedSheets = [];

    /**
     * @param string|array $sheets
     *
     * @return $this
     */
    public function onlySheets($sheets)
    {
        $this->conditionallySelectedSheets = is_array($sheets) ? $sheets : func_get_args();

        return $this;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return \array_filter($this->conditionalSheets(), function ($name) {
            return \in_array($name, $this->conditionallySelectedSheets, false);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return array
     */
    abstract public function conditionalSheets(): array;
}
