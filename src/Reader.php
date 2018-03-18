<?php

namespace Maatwebsite\Excel;

use Illuminate\Filesystem\FilesystemManager;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Reader
{
    use DelegatedMacroable, HasEventBus;

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var FilesystemManager
     */
    private $filesystem;

    /**
     * @param FilesystemManager $filesystem
     */
    public function __construct(FilesystemManager $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param object      $import
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $readerType
     *
     * @return bool
     */
    public function read($import, $filePath, $disk = null, $readerType = null)
    {
        $pathinfo = pathinfo($filePath);

        $file = $this->filesystem->disk($disk)->get($filePath);
        $tmp  = sys_get_temp_dir() . '/' . str_random(16) . '.' . $pathinfo['extension'];

        file_put_contents($tmp, $file);

        $readerType = $readerType ?? IOFactory::identify($tmp);
        $reader = IOFactory::createReader($readerType);

        if (!$reader->canRead($tmp)) {
            dd('nope');
        }

        $this->spreadsheet = $reader->load($tmp);

        dd($this->spreadsheet->getActiveSheet()->toArray());

        return true;
    }

    /**
     * @return object
     */
    public function getDelegate()
    {
        return $this->spreadsheet;
    }
}
