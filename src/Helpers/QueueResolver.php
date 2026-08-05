<?php

namespace Maatwebsite\Excel\Helpers;

use Illuminate\Queue\Attributes\Connection;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\ReadsQueueAttributes;
use Maatwebsite\Excel\Concerns\Import;

class QueueResolver
{
    /**
     * Resolve the queue an import should be dispatched on. Honours a #[Queue]
     * attribute exactly like Laravel resolves it for regular jobs (including
     * backed-enum values), falling back to a plain `queue` property.
     */
    public static function queue(Import $import): mixed
    {
        return self::resolve($import, Queue::class, 'queue');
    }

    /**
     * Resolve the connection an import should be dispatched on. Honours a
     * #[Connection] attribute, falling back to a plain `connection` property.
     */
    public static function connection(Import $import): mixed
    {
        return self::resolve($import, Connection::class, 'connection');
    }

    private static function resolve(Import $import, string $attribute, string $property): mixed
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

            public function read(Import $target, string $attribute, string $property): mixed
            {
                return $this->getAttributeValue($target, $attribute, $property);
            }
        };
    }
}
