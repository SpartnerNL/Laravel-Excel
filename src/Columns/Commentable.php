<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

// TODO: shouldn't this be conditional based on cell / callback?
trait Commentable
{
    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @var string|null
     */
    protected $commentAuthor;

    /**
     * @var callable|null
     */
    protected $commentCallback;

    /**
     * @param string        $comment
     * @param string|null   $author
     * @param callable|null $callback
     *
     * @return $this
     */
    public function comment(string $comment, string $author = null, callable $callback = null)
    {
        $this->comment         = $comment;
        $this->commentAuthor   = $author;
        $this->commentCallback = $callback;

        return $this;
    }

    /**
     * @param Cell  $cell
     * @param mixed $data
     */
    protected function writeComment(Cell $cell, $data)
    {
        if (!$this->comment) {
            return;
        }

        $cellComment = $cell->getWorksheet()->getComment(
            $cell->getCoordinate()
        );

        $cellComment->getText()->createText($this->comment);

        if ($this->commentAuthor) {
            $cellComment->setAuthor($this->commentAuthor);
        }

        if (is_callable($this->commentCallback)) {
            ($this->commentCallback)($cellComment);
        }
    }
}
