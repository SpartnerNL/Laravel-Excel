<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Helpers;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Helpers\ConcernTree;
use Maatwebsite\Excel\Tests\TestCase;

final class ConcernTreeTest extends TestCase
{
    public function test_flatten_null_returns_empty_storage(): void
    {
        $result = ConcernTree::flatten(null);

        $this->assertCount(0, $result);
    }

    public function test_flatten_leaf_without_multiple_sheets_returns_just_the_leaf(): void
    {
        $leaf = new class implements Import
        {
        };

        $result = ConcernTree::flatten($leaf);

        $this->assertCount(1, $result);
        $this->assertTrue($result->offsetExists($leaf));
    }

    public function test_flatten_normal_tree_returns_all_unique_nodes(): void
    {
        $child1 = new class implements Import
        {
        };
        $child2 = new class implements Import
        {
        };
        $root = new readonly class($child1, $child2) implements Import, WithMultipleSheets
        {
            public function __construct(private Import $a, private Import $b)
            {
            }

            /**
             * @return Import[]
             */
            public function sheets(): array
            {
                return [$this->a, $this->b];
            }
        };

        $result = ConcernTree::flatten($root);

        $this->assertCount(3, $result);
        $this->assertTrue($result->offsetExists($root));
        $this->assertTrue($result->offsetExists($child1));
        $this->assertTrue($result->offsetExists($child2));
    }

    public function test_flatten_identity_cycle_does_not_loop(): void
    {
        $root = new class implements Import, WithMultipleSheets
        {
            /** @return array<int, static> */
            public function sheets(): array
            {
                return [$this];
            }
        };

        $result = ConcernTree::flatten($root);

        $this->assertCount(1, $result);
        $this->assertTrue($result->offsetExists($root));
    }

    public function test_flatten_new_self_cycle_does_not_loop(): void
    {
        $root = new class implements Import, WithMultipleSheets
        {
            /** @return array<int, static> */
            public function sheets(): array
            {
                return [new self];
            }
        };

        $result = ConcernTree::flatten($root);

        // Root is collected; the new instance is stopped by the class-ancestry guard.
        $this->assertCount(1, $result);
        $this->assertTrue($result->offsetExists($root));
    }

    public function test_flatten_indirect_identity_cycle_does_not_loop(): void
    {
        $b = new class implements Import, WithMultipleSheets
        {
            public ?Import $parent = null;

            public function sheets(): array
            {
                return [$this->parent];
            }
        };
        $root = new readonly class($b) implements Import, WithMultipleSheets
        {
            public function __construct(private Import $child)
            {
            }

            /**
             * @return Import[]
             */
            public function sheets(): array
            {
                return [$this->child];
            }
        };
        $b->parent = $root;

        $result = ConcernTree::flatten($root);

        $this->assertCount(2, $result);
        $this->assertTrue($result->offsetExists($root));
        $this->assertTrue($result->offsetExists($b));
    }

    public function test_flatten_indirect_class_cycle_does_not_loop(): void
    {
        // Use two named classes so that get_class() returns different names,
        // but ClassB instantiates ClassA — an indirect class cycle.
        $root = new ClassA;

        $result = ConcernTree::flatten($root);

        // ClassA and ClassB are both collected; the second ClassA instance is stopped.
        $this->assertCount(2, $result);
    }

    public function test_flatten_sibling_sheets_of_same_class_are_both_collected(): void
    {
        $sheet1 = new class implements Import
        {
        };
        $sheet2 = new class implements Import
        {
        };

        // Same anonymous class, different instances — use a concrete wrapper.
        $root = new readonly class($sheet1, $sheet2) implements Import, WithMultipleSheets
        {
            public function __construct(private Import $a, private Import $b)
            {
            }

            /**
             * @return Import[]
             */
            public function sheets(): array
            {
                return [$this->a, $this->b];
            }
        };

        $result = ConcernTree::flatten($root);

        $this->assertCount(3, $result);
        $this->assertTrue($result->offsetExists($sheet1));
        $this->assertTrue($result->offsetExists($sheet2));
    }
}

// Named concrete classes so that get_class() returns stable, distinct names.

class ClassA implements Import, WithMultipleSheets
{
    /**
     * @return ClassB[]
     */
    public function sheets(): array
    {
        return [new ClassB];
    }
}

class ClassB implements Import, WithMultipleSheets
{
    /**
     * @return ClassA[]
     */
    public function sheets(): array
    {
        return [new ClassA];
    }
}
