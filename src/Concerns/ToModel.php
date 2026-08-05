<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Model;

interface ToModel extends Import
{
    /**
     * @param  array<array-key, mixed>  $row
     * @return Model|array<int, Model>|null
     */
    public function model(array $row): Model|array|null;
}
