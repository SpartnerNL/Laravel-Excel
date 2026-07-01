<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Model;

interface ToModel
{
    /**
     * @param  array<array-key, mixed>  $row
     * @return Model|array<int, Model>|null
     */
    public function model(array $row): Model|array|null;
}
