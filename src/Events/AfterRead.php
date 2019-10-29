<?php
<<<<<<< HEAD

=======
>>>>>>> 3.1
namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Reader;

class AfterRead extends Event
{
    /**
     * @var Reader
     */
    public $reader;

    /**
     * @var object
     */
    private $importable;
<<<<<<< HEAD
    
=======

>>>>>>> 3.1
    /**
     * @param Reader $reader
     * @param object $importable
     */
    public function __construct(Reader $reader, $importable)
    {
        $this->reader     = $reader;
        $this->importable = $importable;
<<<<<<< HEAD
    }    
=======
    }
>>>>>>> 3.1

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
     * @return mixed
     */
    public function getDelegate()
    {
<<<<<<< HEAD
        return $this->reader->getPhpSpreadsheetReader();
    }
=======
        // return $this->reader;
        return $this->reader->getPhpSpreadsheetReader();
		/*
		 * This will give Access to the Methods:
		 *	- 	phpSpreadsheet->setLoadSheetsOnly([<$sheetname>...]);
         *  -	phpSpreadsheet->setLoadAllSheets();
		 *
		 *  -	phpSpreadsheet->setReadDataOnly(<true|false>);
         *  -	phpSpreadsheet->setReadEmptyCells(<true|false>);
         *  -	phpSpreadsheet->setIncludeCharts(<true|false>);
		*/
    }

>>>>>>> 3.1
}
