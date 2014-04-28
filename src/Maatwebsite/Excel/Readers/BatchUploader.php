<?php namespace Maatwebsite\Excel\Readers;

use Maatwebsite\Excel\Exceptions\LaravelExcelException;

class Batch {

    /**
     * Batch files
     * @var [type]
     */
    public $files = array();

    /**
     * Set allowed file extensions
     * @var array
     */
    protected $allowedFileExtensions = array(
        'xls', 'xlsx', 'csv'
    );

    /**
     * Constructor
     * @param [type] $files [description]
     */
    public function __construct($files)
    {
        // Set files
        $this->_setFiles($files);
    }

    /**
     * Get the files
     * @return [type] [description]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set the batch files
     * @param [type] $files [description]
     */
    public function _setFiles($files)
    {
        // If the param is an array, these will be the files for the batch import
        if(is_array($files))
        {
            $this->files = $this->_getFilesByArray($files);
        }

        // Get all the files inside a folder
        elseif(is_string($files))
        {
            $this->files = $this->_getFilesByFolder($files);
        }

        // Check if files were found
        if(empty($this->files))
            throw new LaravelExcelException('[ERROR]: No files were found. Batch terminated.');
    }

    /**
     * Set files by array
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    protected function _getFilesByArray($array)
    {
        // Make sure we have real paths
        foreach($array as $i => $file)
        {
            $this->files[$i] = realpath($file) ? $file : base_path($file);
        }
    }

    /**
     * Get all files inside a folder
     * @param  [type] $folder [description]
     * @return [type]         [description]
     */
    protected function _getFilesByFolder($folder)
    {
        // Check if it's a real path
        if(!realpath($folder))
            $folder = base_path($folder);

        // Find path names matching our pattern of excel extensions
        $glob = glob($folder.'/*.{'. implode(',', $this->allowedFileExtensions) .'}', GLOB_BRACE);

        if ($glob === false) return array();

        return array_filter($glob, function($file) {
            return filetype($file) == 'file';
        });
    }
}