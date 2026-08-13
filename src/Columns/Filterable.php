<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column as FilterColumn;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Filterable
{
    protected ?string $filter = null;

    /**
     * @var array<string, mixed>
     */
    protected array $filterRules = [];

    /**
     * @param  array<string, mixed>  $rules
     */
    public function autoFilter(array $rules = [], string $filter = FilterColumn::AUTOFILTER_FILTERTYPE_FILTER): static
    {
        $this->filter      = $filter;
        $this->filterRules = $rules;

        return $this;
    }

    public function writeFilters(Worksheet $worksheet): void
    {
        if ($this->filter === null || ($this->filter === FilterColumn::AUTOFILTER_FILTERTYPE_FILTER && count($this->filterRules) === 0)) {
            return;
        }

        $columnFilter = $worksheet->getAutoFilter()->getColumn($this->letter);
        $columnFilter->setFilterType($this->filter);

        foreach ($this->filterRules as $operator => $rules) {
            foreach (Arr::wrap($rules) as $rule) {
                $columnFilter->createRule()->setRule($operator, $rule);
            }
        }
    }

    public function hasAutoFilter(): bool
    {
        return $this->filter !== null;
    }
}
