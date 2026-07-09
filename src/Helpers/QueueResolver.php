<?php

namespace Maatwebsite\Excel\Helpers;

use Illuminate\Queue\Attributes\Connection;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\ReadsQueueAttributes;

class QueueResolver
{
    /**
     * Resolve the queue an import should be dispatched on. Honours a #[Queue]
     * attribute exactly like Laravel resolves it for regular jobs (including
     * backed-enum values), falling back to a plain `queue` property.
     *
     * @param  object  $import
     * @return mixed
     */
    public static function queue($import)
    {
        return self::resolve($import, Queue::class, 'queue');
    }

    /**
     * Resolve the connection an import should be dispatched on. Honours a
     * #[Connection] attribute, falling back to a plain `connection` property.
     *
     * @param  object  $import
     * @return mixed
     */
    public static function connection($import)
    {
        return self::resolve($import, Connection::class, 'connection');
    }

    /**
     * @param  object  $import
     * @return mixed
     */
    private static function resolve($import, string $attribute, string $property)
    {
        // Laravel only reads these attributes off the job it dispatches, never
        // off the import, so we have to read them ourselves. When the framework
        // provides its attribute reader we reuse it, so resolution (including
        // backed-enum values) matches how regular queued jobs are dispatched.
        // Older versions without the reader fall back to a plain property.
        if (trait_exists(ReadsQueueAttributes::class)) {
            return self::reader()->read($import, $attribute, $property);
        }

        return property_exists($import, $property) ? $import->{$property} : null;
    }

    private static function reader(): object
    {
        return new class
        {
            use ReadsQueueAttributes;

            /**
             * @param  object  $target
             * @return mixed
             */
            public function read($target, string $attribute, string $property)
            {
                return $this->getAttributeValue($target, $attribute, $property);
            }
        };
    }
}
