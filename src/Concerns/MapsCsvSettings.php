<?php

namespace Maatwebsite\Excel\Concerns;

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
        $this->delimiter            = array_get($config, 'delimiter', $this->delimiter);
        $this->enclosure            = array_get($config, 'enclosure', $this->enclosure);
        $this->lineEnding           = array_get($config, 'line_ending', $this->lineEnding);
        $this->useBom               = array_get($config, 'use_bom', $this->useBom);
        $this->includeSeparatorLine = array_get($config, 'include_separator_line', $this->includeSeparatorLine);
        $this->excelCompatibility   = array_get($config, 'excel_compatibility', $this->excelCompatibility);
        $this->escapeCharacter      = array_get($config, 'escape_character', $this->escapeCharacter);
        $this->contiguous           = array_get($config, 'contiguous', $this->contiguous);
        $this->inputEncoding        = array_get($config, 'input_encoding', $this->inputEncoding);
    }
}
