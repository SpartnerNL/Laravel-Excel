<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\Writer;
use PHPUnit\Framework\TestCase;

class ExcelTest extends TestCase
{
    /**
     * @var Writer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $writer;

    /**
     * @var Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reader;

    /**
     * @var Excel
     */
    protected $excel;

    public function setUp()
    {
        $this->writer = $this->getMockForAbstractClass(Writer::class);
        $this->reader = $this->getMockForAbstractClass(Reader::class);

        $this->excel = new Excel(
            $this->writer,
            $this->reader
        );
    }

    /**
     * @test
     */
    public function excel_can_load_a_file()
    {
        $filepath = 'filepath';
        $callback = function () {
        };

        $this->reader->method('load')->with($filepath, $callback)->willReturnSelf();

        $reader = $this->excel->load($filepath, $callback);

        $this->assertInstanceOf(Reader::class, $reader);
    }

    /**
     * @test
     */
    public function excel_can_write_a_file()
    {
        $callback = function () {
        };

        $this->writer->method('create')->with($callback)->willReturnSelf();

        $reader = $this->excel->create($callback);

        $this->assertInstanceOf(Writer::class, $reader);
    }
}