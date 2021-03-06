<?php

namespace Maatwebsite\Excel\Columns;

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

    public function autoFilter(array $rules = [])
    {
        $this->filter      = FilterColumn::AUTOFILTER_FILTERTYPE_FILTER;
        $this->filterRules = $rules;

        return $this;
    }

    public function writeFilters(Worksheet $worksheet)
    {
        if (!$this->filter) {
            return;
        }

        $worksheet->setAutoFilter(
            $worksheet->calculateWorksheetDimension()
        );

        $columnFilter = $worksheet->getAutoFilter()->getColumn($this->letter);
        $columnFilter->setFilterType($this->filter);

        foreach ($this->filterRules as $operator => $value) {
            $columnFilter->createRule()->setRule($operator, $value);
        }
    }
}
