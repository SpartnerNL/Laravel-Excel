<?php

namespace Maatwebsite\Excel\Drivers\Spout;

use Maatwebsite\Excel\Spreadsheet;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Reader as ReaderInterface;

class Reader implements ReaderInterface
{
    /**
     * @var callable
     */
    protected $spreadsheetLoader;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     * @param callable      $spreadsheetLoader
     */
    public function __construct(Configuration $configuration, callable $spreadsheetLoader)
    {
        $this->setLoader($spreadsheetLoader);
        $this->configuration = $configuration;
    }

    /**
     * @param string        $filePath
     * @param callable|null $callback
     *
     * @return Spreadsheet
     */
    public function load(string $filePath, callable $callback = null): Spreadsheet
    {
        if (is_callable($callback)) {
            $callback($this, $filePath);
        }
        //return $this;
    }

    /**
     * @param callable $spreadsheetLoader
     *
     * @return ReaderInterface
     */
    public function setLoader(callable $spreadsheetLoader): ReaderInterface
    {
        $this->spreadsheetLoader = $spreadsheetLoader;

        return $this;
    }

    /**
     * @return callable
     */
    public function getLoader(): callable
    {
        return $this->spreadsheetLoader;
    }
}
