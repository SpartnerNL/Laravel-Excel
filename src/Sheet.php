<?php

namespace Maatwebsite\Excel;

interface Sheet
{
    /**
     * @return Row
     */
    public function first(): Row;
}
