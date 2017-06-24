<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use PHPUnit\Framework\TestCase;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Cell;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders\DefaultLoader;

class CellTest extends TestCase
{
    /**
     * @var string
     */
    protected static $simpleXlsx = __DIR__ . '/../../_data/simple_xlsx.xlsx';

    /**
     * @var Cell
     */
    protected static $cachedCell;

    /**
     * @var Cell
     */
    protected $cell;

    public static function setUpBeforeClass()
    {
        static::$cachedCell = (new Reader(new Configuration(), new DefaultLoader()))
            ->load(static::$simpleXlsx)
            ->sheetByIndex(0)
            ->cell('C3');
    }

    public function setUp()
    {
        parent::setUp();

        $this->cell = clone static::$cachedCell;
    }

    /**
     * @test
     */
    public function can_get_cell_value()
    {
        $this->assertEquals('C3', $this->cell->getValue());
    }

    /**
     * @test
     */
    public function can_get_coordinate()
    {
        $this->assertEquals('C3', $this->cell->getCoordinate());
    }

    /**
     * @test
     */
    public function can_get_column_index()
    {
        $this->assertEquals('C', $this->cell->getColumn());
    }

    /**
     * @test
     */
    public function can_get_row_index()
    {
        $this->assertEquals(3, $this->cell->getRow());
    }

    /**
     * @test
     */
    public function column_stringable_casts_to_cell_value()
    {
        $this->assertEquals($this->cell->getValue(), (string) $this->cell);
    }
}
