<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Model;

interface ToModel
{
    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row);
}
