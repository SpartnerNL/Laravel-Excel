<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithFreezePane extends Export
{
    /**
     * The cell coordinate that becomes the top-left of the scrolling region.
     * 'A2' freezes the first row; 'B1' freezes column A; 'B2' freezes both.
     */
    public function freezePane(): string;
}
