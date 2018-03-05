<?php

namespace Maatwebsite\Excel;

trait HasEventBus
{
    /**
     * @var array
     */
    protected $events = [];

    /**
     * @param array $listeners
     */
    public function registerListeners(array $listeners)
    {
        foreach ($listeners as $event => $listener) {
            $this->listen($event, $listener);
        }
    }

    /**
     * @param string   $event
     * @param callable $listener
     */
    public function listen(string $event, callable $listener)
    {
        $this->events[$event][] = $listener;
    }

    /**
     * @param object $event
     */
    public function raise($event)
    {
        $listeners = $this->events[get_class($event)] ?? [];

        foreach ($listeners as $listener) {
            $listener($event);
        }
    }
}
