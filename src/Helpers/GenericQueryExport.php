<?php

namespace Maatwebsite\Excel\Helpers;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GenericQueryExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @var \Illuminate\Database\Query\Builder
     */
    private $_query;

    /**
     * @var array<string>
     */
    private $_headings;

    /**
     * @var callable
     */
    private $_mappings;

    /**
     * forQuery
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $headings
     * @param callable|null $mappings
     */
    public function __construct($query, $headings = [], $mappings = null)
    {
        $this->_query = $query;
        $this->_headings = $headings;
        if ($mappings) {
            $this->_mappings = $mappings;
        }
    }

    public function headings(): array
    {
        return array_values($this->_headings);
    }

    public function map($row): array
    {
        if ($this->_mappings) {
            return call_user_func($this->_mappings, $row);
        } elseif ($this->_headings) {
            $mapping = [];
            foreach ($this->_headings as $k => $_) {
                $v = $row;
                foreach (explode('.', $k) as $k2) {
                    $v = $v[$k2] ?? null;
                }
                $mapping[] = $v;
            }
            return array_values($mapping);
        }
        return (array) $row;
    }

    /**
    * @return \Illuminate\Database\Query\Builder
    */
    public function query()
    {
        return $this->_query;
    }
}
