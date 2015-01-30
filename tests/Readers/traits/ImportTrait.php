<?php

trait ImportTrait {

    /**
     * Test csv file
     * @var [type]
     */
    protected $file;

    /**
     * Loaded csv file
     * @var [type]
     */
    protected $loadedFile;

    /**
     * Setup
     */
    public function setUp()
    {
        parent::setUp();

        // Set default heading
        Config::set('excel.import.heading', 'slugged');

        // Set excel class
        $this->excel    = App::make('phpexcel');

        // Set writer class
        $this->reader   = App::make('excel.reader');
        $this->reader->injectExcel($this->excel);

        // Disable heading usage
        if(isset($this->noHeadings) && $this->noHeadings)
            $this->reader->noHeading(true);

        // Load csv file
        $this->loadFile();
    }

    /**
     * Test loading a csv file
     * @return [type] [description]
     */
    public function testLoadFile()
    {
        $this->assertEquals($this->reader, $this->loadedFile);
        $this->assertInstanceOf('PHPExcel', $this->reader->getExcel());
    }

    /**
     * Load a csv file
     * @return [type] [description]
     */
    protected function loadFile()
    {
        // Set test csv file
        $this->file = __DIR__ . '/..' . DIRECTORY_SEPARATOR . $this->fileName;

        // Loaded csv
        $this->loadedFile = $this->reader->load($this->file);
    }

    /**
     * Load a csv file
     * @return [type] [description]
     */
    protected function reload()
    {
        // Set test csv file
        $this->file = __DIR__ . '/..' . DIRECTORY_SEPARATOR . $this->fileName;

        // Loaded csv
        return $this->reader->load($this->file);
    }
}