<?php

namespace Maatwebsite\Excel;

trait HasEventBus
{
    /**
     * @var array
     */
    protected static $globalEvents = [];

    /**
     * @var array
     */
    protected $events = [];

    /**
     * Register local event listeners.
     *
     * @param array $listeners
     */
    public function registerListeners(array $listeners)
    {
        foreach ($listeners as $event => $listener) {
            $this->events[$event][] = $listener;
        }
    }

    /**
     * Register a global event listener.
     *
     * @param string   $event
     * @param callable $listener
     */
    public static function listen(string $event, callable $listener)
    {
        static::$globalEvents[$event][] = $listener;
    }

    /**
     * @param object $event
     */
    public function raise($event)
    {
        foreach ($this->listeners($event) as $listener) {
            $listener($event);
        }
    }

    /**
     * @param object $event
     *
     * @return callable[]
     */
    public function listeners($event): array
    {
        $name = \get_class($event);

        $localListeners  = $this->events[$name] ?? [];
        $globalListeners = static::$globalEvents[$name] ?? [];

        return array_merge($globalListeners, $localListeners);
    }
}
