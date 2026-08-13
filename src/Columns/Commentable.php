<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Closure;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

trait Commentable
{
    protected string|Closure|null $comment = null;

    protected ?string $commentAuthor = null;

    protected ?Closure $commentCallback = null;

    /**
     * Add a comment to every cell in this column. Pass a closure to derive the
     * comment from the row, returning null to leave a cell without one.
     */
    public function comment(string|Closure $comment, ?string $author = null, ?Closure $callback = null): static
    {
        $this->comment         = $comment;
        $this->commentAuthor   = $author;
        $this->commentCallback = $callback;

        return $this;
    }

    protected function writeComment(Cell $cell, mixed $data): void
    {
        if ($this->comment === null) {
            return;
        }

        $comment = $this->comment instanceof Closure
            ? ($this->comment)($data)
            : $this->comment;

        if ($comment === null || $comment === '') {
            return;
        }

        $cellComment = $cell->getWorksheet()->getComment($cell->getCoordinate());

        $cellComment->getText()->createText((string) $comment);

        if ($this->commentAuthor !== null) {
            $cellComment->setAuthor($this->commentAuthor);
        }

        if ($this->commentCallback instanceof Closure) {
            ($this->commentCallback)($cellComment, $data);
        }
    }
}
