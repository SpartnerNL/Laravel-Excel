<?php
<<<<<<< HEAD

=======
>>>>>>> 3.1
namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Reader;

class BeforeRead extends Event
{
    /**
     * @var Reader
     */
    public $reader;

    /**
     * @var object
     */
    private $importable;

    /**
     * @param Reader $reader
     * @param object $importable
     */
    public function __construct(Reader $reader, $importable)
    {
        $this->reader     = $reader;
        $this->importable = $importable;
    }

    /**
     * @return Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }

    /**
     * @return object
     */
    public function getConcernable()
    {
        return $this->importable;
    }

    /**
<<<<<<< HEAD
     * @return mixed
=======
     * @return phpSpreadsheet Reader
>>>>>>> 3.1
     */
    public function getDelegate()
    {
        return $this->reader->getPhpSpreadsheetReader();
		/*
<<<<<<< HEAD
		 * This will give Access to the phpSpreadsheet Methods:
=======
		 * This will give Access to the Methods:
>>>>>>> 3.1
		 *	- 	phpSpreadsheet->setLoadSheetsOnly([<$sheetname>...]);
         *  -	phpSpreadsheet->setLoadAllSheets();
		 *
		 *  -	phpSpreadsheet->setReadDataOnly(<true|false>);
         *  -	phpSpreadsheet->setReadEmptyCells(<true|false>);
         *  -	phpSpreadsheet->setIncludeCharts(<true|false>);
		*/
    }
<<<<<<< HEAD
		
=======

>>>>>>> 3.1
}
