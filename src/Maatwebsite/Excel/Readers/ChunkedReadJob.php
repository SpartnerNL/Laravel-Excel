<?php

namespace Maatwebsite\Excel\Readers;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Filters\ChunkReadFilter;
use SuperClosure\Serializer;

class ChunkedReadJob implements SelfHandling, ShouldQueue
{
    /**
     * @var int
     */
    private $startRow;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var
     */
    private $chunkSize;

    /**
     * @var
     */
    private $startIndex;

    /**
     * @var
     */
    private $file;

    /**
     * @var null
     */
    private $sheets;

    /**
     * @var bool
     */
    private $shouldQueue;

    /**
     * ChunkedReadJob constructor.
     *
     * @param          $file
     * @param null     $sheets
     * @param int      $startRow
     * @param          $startIndex
     * @param          $chunkSize
     * @param callable $callback
     * @param bool     $shouldQueue
     */
    public function __construct(
        $file,
        $sheets = null,
        $startRow,
        $startIndex,
        $chunkSize,
        callable $callback,
        $shouldQueue = true
    ) {
        $this->startRow   = $startRow;
        $this->chunkSize  = $chunkSize;
        $this->startIndex = $startIndex;
        $this->file       = $file;

        $this->callback    = $shouldQueue ? (new Serializer)->serialize($callback) : $callback;
        $this->sheets      = $sheets;
        $this->shouldQueue = $shouldQueue;
    }

    /***
     * Handle the read job
     */
    public function handle()
    {
        $reader = app('excel.reader');
        $reader->injectExcel(app('phpexcel'));
        $reader->_init($this->file);

        $filter = new ChunkReadFilter();
        $reader->reader->setLoadSheetsOnly($this->sheets);
        $reader->reader->setReadFilter($filter);
        $reader->reader->setReadDataOnly(true);

        // Set the rows for the chunking
        $filter->setRows($this->startRow, $this->chunkSize);

        // Load file with chunk filter enabled
        $reader->excel = $reader->reader->load($this->file);

        // Slice the results
        $results = $reader->get()->slice($this->startIndex, $this->chunkSize);

        $callback = $this->shouldQueue ? (new Serializer)->unserialize($this->callback) : $this->callback;

        // Do a callback
        if (is_callable($callback)) {
            $break = call_user_func($callback, $results);
        }

        $reader->_reset();
        unset($reader, $results);

        if ($break) {
            return true;
        }
    }
}
