<?php

namespace Maatwebsite\Excel\Jobs\Middleware;

use Closure;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Support\Traits\Localizable;

class LocalizeJob
{
    use Localizable;

    /**
     * @var object
     */
    private $localizable;

    /**
     * LocalizeJob constructor.
     *
     * @param object $localizable
     */
    public function __construct($localizable)
    {
        $this->localizable = $localizable;
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
            if ($this->localizable instanceof HasLocalePreference) {
                return $this->localizable->preferredLocale();
            }

            return null;
        });

        return $this->withLocale($locale, function () use ($next, $job) {
            return $next($job);
        });
    }
}
