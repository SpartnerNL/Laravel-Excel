<?php

namespace Maatwebsite\Excel\Jobs\Middleware;

use Closure;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Support\Traits\Localizable;

class LocalizeJob
{
    use Localizable;

    /**
     * LocalizeJob constructor.
     *
     * @param  object  $localizable
     */
    public function __construct(
        private $localizable,
    ) {
    }

    /**
     * Handles the job.
     */
    public function handle(mixed $job, Closure $next): mixed
    {
        $locale = value(function () {
            if ($this->localizable instanceof HasLocalePreference) {
                return $this->localizable->preferredLocale();
            }

            return null;
        });

        return $this->withLocale($locale, fn () => $next($job));
    }
}
