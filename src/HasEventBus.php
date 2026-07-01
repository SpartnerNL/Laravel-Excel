<?php

namespace Maatwebsite\Excel;

trait HasEventBus
{
    /**
     * @var array<string, array<int, callable>>
     */
    protected static array $globalEvents = [];

    /**
     * @var array<string, array<int, callable>>
     */
    protected array $events = [];

    /**
     * Register local event listeners.
     *
     * @param  array<string, callable>  $listeners
     */
    public function registerListeners(array $listeners): void
    {
        foreach ($listeners as $event => $listener) {
            $this->events[$event][] = $listener;
        }
    }

    public function clearListeners(): void
    {
        $this->events = [];
    }

    /**
     * Register a global event listener.
     */
    public static function listen(string $event, callable $listener): void
    {
        static::$globalEvents[$event][] = $listener;
    }

    public function raise(object $event): void
    {
        foreach ($this->listeners($event) as $listener) {
            $listener($event);
        }
    }

    /**
     * @return callable[]
     */
    public function listeners(object $event): array
    {
        $name = $event::class;

        $localListeners  = $this->events[$name] ?? [];
        $globalListeners = static::$globalEvents[$name] ?? [];

        return array_merge($globalListeners, $localListeners);
    }
}
