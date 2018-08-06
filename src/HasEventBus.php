<?php

namespace Maatwebsite\Excel;

trait HasEventBus
{
    /**
     * @var array
     */
    protected static $events = [];

    /**
     * @param array $listeners
     */
    public static function registerListeners(array $listeners)
    {
        foreach ($listeners as $event => $listener) {
            static::listen($event, $listener);
        }
    }

    /**
     * @param string   $event
     * @param callable $listener
     */
    public static function listen(string $event, callable $listener)
    {
        static::$events[$event][] = $listener;
    }

    /**
     * @param object $event
     */
    public function raise($event)
    {
        $listeners = static::$events[\get_class($event)] ?? [];

        foreach ($listeners as $listener) {
            $listener($event);
        }
    }
}
