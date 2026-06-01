<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Queue\Attributes\Connection;
use Illuminate\Queue\Attributes\Queue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ShouldQueueWithoutChain;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\Enums\QueueChannel;

#[Queue(QueueChannel::EXCEL_IMPORTS)]
#[Connection('redis')]
class QueuedImportWithQueueAttribute implements ShouldQueueWithoutChain, ToModel, WithChunkReading
{
    use Importable;

    public function model(array $row): User
    {
        return new User([
            'name'     => $row[0],
            'email'    => $row[1],
            'password' => 'secret',
        ]);
    }

    public function chunkSize(): int
    {
        return 1;
    }
}
