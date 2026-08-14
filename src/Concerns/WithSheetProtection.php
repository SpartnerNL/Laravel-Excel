<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithSheetProtection extends Export
{
    /**
     * Return a password to protect the sheet with a password,
     * or null to lock the sheet without requiring a password.
     */
    public function sheetProtection(): ?string;
}
