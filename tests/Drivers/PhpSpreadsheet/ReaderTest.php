<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var string
     */
    protected $simpleFile = __DIR__ . '/../../_data/simple_xlsx.xlsx';

    /**
     * @var Reader
     */
    protected $reader;

    public function setUp()
    {
        parent::setUp();

        $this->reader = new Reader;
    }

    public function test_reader_can_load_sheet_by_index()
    {
        $reader = $this->reader->load(__DIR__ . '/../../_data/simple_xlsx.xlsx');
        $sheet  = $reader->sheetByIndex(0);

        $this->assertInstanceOf(Sheet::class, $sheet);
    }
}