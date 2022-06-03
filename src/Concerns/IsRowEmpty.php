<?php

namespace Maatwebsite\Excel\Concerns;

interface IsRowEmpty
{
    public function isEmptyWhen(array $row);
}
