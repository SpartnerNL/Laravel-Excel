<?php

namespace Maatwebsite\Excel;


class Excel extends \PHPExcel
{

    public $i = 0;
    public $excel;
    public $object;
    public $title;
    public $ext;
    public $format;

    public function __construct()
    {

        parent::__construct();

        // Init the PHP excel class
        $this->excel = new \PHPExcel();
    }

    public function create($title)
    {

        // Set file title
        $this->title = $title;

        // Set properties
        $this->excel->getProperties()
                    ->setCreator(\Config::get('Maatwebsite/excel::creator'))
                    ->setTitle($this->title);

        return $this;

    }

    public function sheet($title, $orientation = 'landscape')
    {

        // Set page orientation
        switch ($orientation) {
            case 'portrait':
                $this->orientation = \PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT;
                break;

            case 'landscape':
                $this->orientation = \PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE;
                break;
        }

        // Create a new sheet
        $this->excel->createSheet();

        // Set to current index based on i
        $this->excel->setActiveSheetIndex($this->i);

        // Change sheet settings
        $this->excel->getActiveSheet()
                        ->setTitle($title)
                    ->getPageSetup()
                        ->setOrientation($this->orientation)
                        ->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4)
                        ->setFitToPage(true)
                        ->setFitToWidth(1)
                        ->setFitToHeight(1);

        // Count number of sheets
        $this->i++;

        // Return itself to chain
        return $this;
    }

    public function with($array)
    {

        // Send the variables to the excel sheet
        $this->excel
                ->getActiveSheet()
                    ->fromArray($array);

        return $this;
    }

    public function export($ext = 'xls')
    {

        // Set the extension
        $this->ext = $ext;

        // Render the XLS
        $this->render();

        // Export the file
        $this->object->save('php://output');

        exit;
    }

    public function save($ext = 'xls')
    {

        // Set the extension
        $this->ext = $ext;

        // Render the XLS
        $this->render();

        // Save the file to specified location
        $this->object->save($this->title . '.' . $this->ext);

        exit;
    }

    private function render()
    {

        // Set the render format
        $this->format = $this->decodeFormat($this->ext);

        // Set to first sheet
        $this->excel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->title . '.'. $this->ext .'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $this->object = \PHPExcel_IOFactory::createWriter($this->excel, $this->format);
    }

    private function decodeFormat($ext)
    {

        switch($ext)
        {

            case 'xls':
                return 'Excel5';
                break;

            case 'csv':
                return 'CSV';
                break;

            default:
                return 'Excel5';
                break;

        }

    }

}