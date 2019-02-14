<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Arr;

trait MapsCsvSettings
{
    /**
     * @var string
     */
    protected $delimiter = ',';

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var string
     */
    protected $lineEnding = PHP_EOL;

    /**
     * @var bool
     */
    protected $useBom = false;

    /**
     * @var bool
     */
    protected $includeSeparatorLine = false;

    /**
     * @var bool
     */
    protected $excelCompatibility = false;

    /**
     * @var string
     */
    protected $escapeCharacter = '\\';

    /**
     * @var bool
     */
    protected $contiguous = false;

    /**
     * @var string
     */
    protected $inputEncoding = 'UTF-8';

    /**
     * @param array $config
     */
    public function applyCsvSettings(array $config)
    {
        $this->delimiter            = Arr::get($config, 'delimiter', $this->delimiter);
        $this->enclosure            = Arr::get($config, 'enclosure', $this->enclosure);
        $this->lineEnding           = Arr::get($config, 'line_ending', $this->lineEnding);
        $this->useBom               = Arr::get($config, 'use_bom', $this->useBom);
        $this->includeSeparatorLine = Arr::get($config, 'include_separator_line', $this->includeSeparatorLine);
        $this->excelCompatibility   = Arr::get($config, 'excel_compatibility', $this->excelCompatibility);
        $this->escapeCharacter      = Arr::get($config, 'escape_character', $this->escapeCharacter);
        $this->contiguous           = Arr::get($config, 'contiguous', $this->contiguous);
        $this->inputEncoding        = Arr::get($config, 'input_encoding', $this->inputEncoding);
    }
}
