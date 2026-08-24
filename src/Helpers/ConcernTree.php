<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Helpers;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use SplObjectStorage;

/**
 * @internal
 */
final class ConcernTree
{
    /**
     * Returns a flat SplObjectStorage of every unique node in the concernable
     * tree (root + all sheet descendants), safe against cycles.
     *
     * Two cycle patterns are handled:
     *  - Identity cycles ($this returned from sheets()): caught by the shared
     *    SplObjectStorage visited set.
     *  - Class-level cycles (new self() or A->B->new A()): caught by tracking
     *    class names along the current ancestry path (array passed by value so
     *    sibling branches are not affected).
     *
     * @return SplObjectStorage<Export|Import, mixed>
     */
    public static function flatten(Export|Import|null $root): SplObjectStorage
    {
        /** @var SplObjectStorage<Export|Import, mixed> $storage */
        $storage = new SplObjectStorage;
        self::collect($root, $storage, []);

        return $storage;
    }

    /**
     * @param  SplObjectStorage<Export|Import, mixed>  $visited
     * @param  array<class-string<Export|Import>, true>  $ancestorClasses
     */
    private static function collect(Export|Import|null $node, SplObjectStorage $visited, array $ancestorClasses): void
    {
        if ($node === null || $visited->contains($node)) {
            return;
        }

        if ($node instanceof WithMultipleSheets) {
            $class = $node::class;

            if (isset($ancestorClasses[$class])) {
                return; // class-level cycle — skip entirely, do not attach
            }

            $visited->attach($node);
            $ancestorClasses[$class] = true;

            foreach ($node->sheets() as $sheet) {
                self::collect($sheet, $visited, $ancestorClasses);
            }
        } else {
            $visited->attach($node);
        }
    }
}
