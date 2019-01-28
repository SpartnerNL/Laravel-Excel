<?php

namespace Maatwebsite\Excel\Concerns;

use InvalidArgumentException;
use Maatwebsite\Excel\Importer;
use Illuminate\Support\Collection;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Validators\Failure;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;

trait SkipsFailures
{
    /**
     * @var Failure[]
     */
    protected $failures = [];

    /**
     * @param Failure ...$failures
     */
    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    /**
     * @return Failure[]|Collection
     */
    public function failures(): Collection
    {
        return new Collection($this->failures);
    }
}
