<?php namespace Maatwebsite\Excel\Filters;

use PHPExcel_Reader_IReadFilter;

class ChunkReadFilter implements PHPExcel_Reader_IReadFilter
{
    /**
     * Start row
     * @var integer
     */
    private $_startRow = 0;

    /**
     * End row
     * @var integer
     */
    private $_endRow = 0;

    /**
     * Set the list of rows that we want to read
     * @param   integer $startRow
     * @param   integer $chunkSize
     * @return  void
     */
    public function setRows($startRow, $chunkSize)
    {
        $this->_startRow = $startRow;
        $this->_endRow   = $startRow + $chunkSize;
    }

    /**
     * Read the cell
     * @param  string   $column
     * @param  integer  $row
     * @param  string   $worksheetName
     * @return booleaan
     */
    public function readCell($column, $row, $worksheetName = '')
    {
        //  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow
        if (($row == 1) || ($row >= $this->_startRow && $row <= $this->_endRow)) {
            return true;
        }
        return false;
    }
}