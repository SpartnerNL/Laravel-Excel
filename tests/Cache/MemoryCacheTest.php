<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Cache;

use Maatwebsite\Excel\Cache\CacheManager;
use Maatwebsite\Excel\Cache\MemoryInterface;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class MemoryCacheTest extends TestCase
{
    public function test_get_returns_default_when_key_is_absent(): void
    {
        $memory = $this->givenMemory();

        $this->assertNull($memory->get('A1'));
        $this->assertSame('default', $memory->get('A1', 'default'));
    }

    public function test_set_and_get_roundtrip(): void
    {
        $memory = $this->givenMemory();

        $this->assertTrue($memory->set('A1', 'A1-value'));
        $this->assertSame('A1-value', $memory->get('A1'));
    }

    public function test_has_returns_true_when_present_and_false_when_absent(): void
    {
        $memory = $this->givenMemory();
        $memory->set('A1', 'A1-value');

        $this->assertTrue($memory->has('A1'));
        $this->assertFalse($memory->has('A2'));
    }

    public function test_delete_removes_a_value(): void
    {
        $memory = $this->givenMemory();
        $memory->set('A1', 'A1-value');

        $this->assertTrue($memory->delete('A1'));
        $this->assertFalse($memory->has('A1'));
    }

    public function test_delete_multiple_removes_several_values(): void
    {
        $memory = $this->givenMemory();
        $memory->setMultiple(['A1' => 'A1-value', 'A2' => 'A2-value', 'A3' => 'A3-value']);

        $this->assertTrue($memory->deleteMultiple(['A1', 'A2']));
        $this->assertFalse($memory->has('A1'));
        $this->assertFalse($memory->has('A2'));
        $this->assertTrue($memory->has('A3'));
    }

    public function test_set_multiple_and_get_multiple_roundtrip(): void
    {
        $memory = $this->givenMemory();

        $this->assertTrue($memory->setMultiple(['A1' => 'A1-value', 'A2' => 'A2-value']));
        $this->assertSame(
            ['A1' => 'A1-value', 'A2' => 'A2-value', 'A3' => null],
            $memory->getMultiple(['A1', 'A2', 'A3'])
        );
    }

    public function test_clear_empties_the_cache(): void
    {
        $memory = $this->givenMemory();
        $memory->setMultiple(['A1' => 'A1-value', 'A2' => 'A2-value']);

        $this->assertTrue($memory->clear());
        $this->assertFalse($memory->has('A1'));
        $this->assertFalse($memory->has('A2'));
    }

    public function test_reached_memory_limit_is_always_false_without_a_configured_limit(): void
    {
        $memory = $this->givenMemory();

        for ($i = 0; $i < 10; $i++) {
            $memory->set("A{$i}", "A{$i}-value");
        }

        $this->assertFalse($memory->reachedMemoryLimit());
    }

    public function test_reached_memory_limit_becomes_true_once_the_limit_is_hit(): void
    {
        $memory = $this->givenMemory(2);

        $this->assertFalse($memory->reachedMemoryLimit());

        $memory->set('A1', 'A1-value');
        $this->assertFalse($memory->reachedMemoryLimit());

        $memory->set('A2', 'A2-value');
        $this->assertTrue($memory->reachedMemoryLimit());
    }

    public function test_flush_returns_all_values_and_empties_the_cache(): void
    {
        $memory = $this->givenMemory();
        $memory->setMultiple(['A1' => 'A1-value', 'A2' => 'A2-value']);

        $this->assertSame(
            ['A1' => 'A1-value', 'A2' => 'A2-value'],
            $memory->flush()
        );
        $this->assertFalse($memory->has('A1'));
        $this->assertFalse($memory->has('A2'));
    }

    public function test_flush_detaches_any_cached_spreadsheet_cells(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'A1-value');
        $cell = $sheet->getCell('A1');

        $this->assertInstanceOf(Worksheet::class, $cell->getWorksheetOrNull());

        $memory = $this->givenMemory();
        $memory->set('A1', $cell);

        $flushed = $memory->flush();

        $this->assertSame($cell, $flushed['A1']);
        $this->assertNotInstanceOf(Worksheet::class, $cell->getWorksheetOrNull());
    }

    private function givenMemory(?int $memoryLimit = null): MemoryInterface
    {
        config()->set('excel.cache.batch.memory_limit', $memoryLimit ?: 60000);

        return $this->app->make(CacheManager::class)->createMemoryDriver();
    }
}
