<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Writer;

interface InteractsWithWriter
{
    /**
     * @param Writer $writer
     */
    public function interact(Writer $writer);
}
