<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;

interface FromQuery
{
    /**
     * Return the builder whose rows should be exported.
     *
     * The export walks this query in chunks via Laravel's `Builder::chunk()`,
     * which paginates with `LIMIT/OFFSET`. For chunked pagination to be safe
     * the query MUST have a deterministic, unique `ORDER BY`. If multiple
     * rows share the primary sort value, the database engine is free to
     * return tied rows in a different order between paginated queries —
     * which causes some rows to appear in two chunks and others to be
     * skipped entirely, with no error raised.
     *
     * Always append a unique tie-breaker — typically the primary key:
     *
     *     return Person::query()
     *         ->orderBy('created_at', 'desc')
     *         ->orderBy('id', 'asc');   // stable tie-breaker
     *
     * UUID primary keys make this pitfall noticeably more likely to bite.
     * With auto-incrementing integer keys, InnoDB stores rows in PK order,
     * which often happens to match insertion order — so ties in
     * `created_at` resolve "naturally" in a stable way and the bug stays
     * latent. With random (v4) UUIDs the storage layout is decorrelated
     * from insertion time and the filesort no longer hides ties; the same
     * code that ran fine for years on integer PKs starts dropping and
     * duplicating rows the day it's deployed against a UUID table. UUIDs
     * have a deterministic lexicographic total order, so `orderBy('id')`
     * still works as a tie-breaker — there's no special handling needed,
     * just always set one.
     *
     * @return Builder|EloquentBuilder<covariant Model>|Relation<covariant Model, covariant Model, Collection<int, Model>>
     */
    public function query(): Builder|EloquentBuilder|Relation;
}
