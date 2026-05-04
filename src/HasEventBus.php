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
     */
    public function registerListeners(array $listeners)
    {
        foreach ($listeners as $event => $listener) {
            $this->events[$event][] = $listener;
        }
    }

    public function clearListeners()
    {
        $this->events = [];
    }

    /**
     * Register a global event listener.
     */
    public static function listen(string $event, callable $listener)
    {
        static::$globalEvents[$event][] = $listener;
    }

    /**
     * @param  object  $event
     */
    public function raise($event)
    {
        foreach ($this->listeners($event) as $listener) {
            $listener($event);
        }
    }

    /**
     * @param  object  $event
     * @return callable[]
     */
    public function listeners($event): array
    {
        $name = $event::class;

        $localListeners  = $this->events[$name] ?? [];
        $globalListeners = static::$globalEvents[$name] ?? [];

        return array_merge($globalListeners, $localListeners);
    }
}
