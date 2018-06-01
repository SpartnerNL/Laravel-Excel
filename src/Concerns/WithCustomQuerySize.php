<?php

namespace Maatwebsite\Excel\Concerns;

interface WithCustomQuerySize
{
    /**
     * Queued exportables are processed in chunks; each chunk being a job pushed to the queue by the QueuedWriter.
     * In case of exportables that implement the FromQuery concern, the number of jobs is calculated by dividing the $query->count() by the chunk size.
     * Depending on the implementation of the query() method (eg. When using a groupBy clause), this calculation might not be correct.
     *
     * When this is the case, you should use this method to provide a custom calculation of the query size.
     *
     * @return int
     */
    public function querySize(): int;
}
