<?php

namespace Maatwebsite\Excel\Jobs\Middleware;

use Closure;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Support\Traits\Localizable;

class SetQueuedExportLocale
{
    use Localizable;

    /**
     * @var object
     */
    protected $export;

    /**
     * SetQueuedExportLocale constructor.
     *
     * @param object $export
     */
    public function __construct($export)
    {
        $this->export = $export;
    }

    /**
     * Handles the job.
     *
     * @param mixed $job
     * @param Closure $next
     * @return mixed
     */
    public function handle($job, Closure $next)
    {
        $locale = value(function () {
            if ($this->export instanceof HasLocalePreference) {
                return $this->export->preferredLocale();
            }

            return null;
        });

        return $this->withLocale($locale, function () use ($next, $job) {
            return $next($job);
        });
    }
}
