<?php

require_once('traits/ImportTrait.php');

class MultipleSheetsXlsReaderTest extends TestCase
{
    /**
     * Import trait.
     */
    use ImportTrait;

    /**
     * Filename.
     * @var string
     */
    protected $fileName = 'files/multiple.xls';

    /**
     * Test get.
     * @return [type] [description]
     */
    public function testGet()
    {
        $got = $this->loadedFile->get();
        $this->assertInstanceOf('Maatwebsite\Excel\Drivers\PHPExcel\Collections\SheetCollection', $got);
        $this->assertCount(2, $got);
    }

    /**
     * Test get.
     * @return [type] [description]
     */
    public function testGetAndGetFirstSheetName()
    {
        $got = $this->loadedFile->get();

        // get first sheet
        $sheet = $got->first();

        // assert sheet title
        $this->assertEquals('Sheet1', $sheet->getTitle());

        // 5 rows
        $this->assertCount(5, $sheet);
    }

    public function testSelectSheet()
    {
        $this->reader->setSelectedSheets('Sheet2');
        $this->reload();

        $sheet = $this->loadedFile->get();

        $this->assertEquals('Sheet2', $sheet->getTitle());
        $this->assertInstanceOf('Maatwebsite\Excel\Drivers\PHPExcel\Collections\RowCollection', $sheet);
        $this->assertCount(5, $sheet);
    }

    public function testSelectSheetByIndex()
    {
        $this->reader->setSelectedSheetIndices([1]);
        $this->reload();

        $sheet = $this->loadedFile->get();

        $this->assertEquals('Sheet2', $sheet->getTitle());
        $this->assertInstanceOf('Maatwebsite\Excel\Drivers\PHPExcel\Collections\RowCollection', $sheet);
        $this->assertCount(5, $sheet);
    }

    public function testSelectMultipleSheets()
    {
        $this->reader->setSelectedSheets(['Sheet1', 'Sheet2']);
        $this->reload();

        $got = $this->loadedFile->get();
        $this->assertInstanceOf('Maatwebsite\Excel\Drivers\PHPExcel\Collections\SheetCollection', $got);
        $this->assertCount(2, $got);

        // get first sheet
        $sheet = $got->first();

        // assert sheet title
        $this->assertEquals('Sheet1', $sheet->getTitle());
        $this->assertInstanceOf('Maatwebsite\Excel\Drivers\PHPExcel\Collections\RowCollection', $sheet);
        $this->assertCount(5, $sheet);
    }

    public function testSelectMultipleSheetsByIndex()
    {
        $this->reader->setSelectedSheetIndices([0, 1]);
        $this->reload();

        $got = $this->loadedFile->get();
        $this->assertInstanceOf('Maatwebsite\Excel\Drivers\PHPExcel\Collections\SheetCollection', $got);
        $this->assertCount(2, $got);

        // get first sheet
        $sheet = $got->first();

        // assert sheet title
        $this->assertEquals('Sheet1', $sheet->getTitle());
        $this->assertInstanceOf('Maatwebsite\Excel\Drivers\PHPExcel\Collections\RowCollection', $sheet);
        $this->assertCount(5, $sheet);
    }
}
