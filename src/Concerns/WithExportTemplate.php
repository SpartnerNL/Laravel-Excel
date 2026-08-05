<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithExportTemplate
{
    public function exportTemplate(): string;
}
