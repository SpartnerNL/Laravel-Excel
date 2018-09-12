<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Model;

interface ToModel
{
    /**
     * @param array $row
     *
     * @return Model
     */
    public function model(array $row): Model;
}
