<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Model;

interface ToModel
{
    /**
     * @return Model|Model[]|null
     */
    public function model(array $row): Model|array|null;
}
