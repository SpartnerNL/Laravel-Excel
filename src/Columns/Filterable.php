<?php

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column as FilterColumn;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Filterable
{
    /**
     * @var string|null
     */
    protected $filter;

    /**
     * @var array
     */
    protected $filterRules = [];

    /**
     * @return $this
     */
    public function autoFilter(array $rules = [], string $filter = FilterColumn::AUTOFILTER_FILTERTYPE_FILTER)
    {
        $this->filter      = $filter;
        $this->filterRules = $rules;

        return $this;
    }

    public function writeFilters(Worksheet $worksheet)
    {
        if (!$this->filter || ($this->filter === FilterColumn::AUTOFILTER_FILTERTYPE_FILTER && 0 === count($this->filterRules))) {
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
        return null !== $this->filter;
    }
}
