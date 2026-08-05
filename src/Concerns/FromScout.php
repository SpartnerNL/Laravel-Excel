<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

interface FromScout extends Export
{
    /**
     * @return Builder<covariant Model>
     */
    public function scout(): Builder;
}
