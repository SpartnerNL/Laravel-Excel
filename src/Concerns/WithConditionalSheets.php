<?php

namespace Maatwebsite\Excel\Concerns;

trait WithConditionalSheets
{
    /**
     * @var array<int, int|string>
     */
    protected array $conditionallySelectedSheets = [];

    /**
     * @param  string|array<int, int|string>  $sheets
     * @return $this
     */
    public function onlySheets(string|array $sheets)
    {
        $this->conditionallySelectedSheets = is_array($sheets) ? $sheets : func_get_args();

        return $this;
    }

    /**
     * @return array<int|string, object>
     */
    public function sheets(): array
    {
        return \array_filter($this->conditionalSheets(), fn ($name): bool => \in_array($name, $this->conditionallySelectedSheets, false), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return array<int|string, object>
     */
    abstract public function conditionalSheets(): array;
}
