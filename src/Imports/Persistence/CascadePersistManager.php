<?php

namespace Maatwebsite\Excel\Imports\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Transactions\TransactionHandler;

/** @todo  */
class CascadePersistManager
{
    /**
     * @var TransactionHandler
     */
    private $transaction;

    public function __construct(TransactionHandler $transaction)
    {
        $this->transaction = $transaction;
    }

    public function persist(Model $model): bool
    {
        return ($this->transaction)(fn () => $this->save($model));
    }

    private function save(Model $model): bool
    {
        if (!$model->save()) {
            return false;
        }

        foreach ($model->getRelations() as $relationName => $models) {
            $models = array_filter(
                $models instanceof Collection ? $models->all() : [$models]
            );

            $relation = $model->{$relationName}();

            if ($relation instanceof BelongsTo) {
                if (!$this->persistBelongsTo($relation, $models)) {
                    return false;
                }
            }

            if ($relation instanceof BelongsToMany) {
                if (!$this->persistBelongsToMany($relation, $models)) {
                    return false;
                }
            }
        }

        // We need to save the model again to
        // make sure all updates are performed.
        $model->save();

        return true;
    }

    private function persistBelongsTo(BelongsTo $relation, array $models): bool
    {
        // With belongs to, we first need to save all relations,
        // so we can use their foreign key to attach to the relation.
        foreach ($models as $model) {
            // Cascade any relations that this child model may have.
            if (!$this->save($model)) {
                return false;
            }

            $relation->associate($model);
        }

        return true;
    }

    private function persistBelongsToMany(BelongsToMany $relation, array $models): bool
    {
        foreach ($models as $model) {
            $relation->save($model);

            // Cascade any relations that this child model may have.
            if (!$this->save($model)) {
                return false;
            }
        }

        return true;
    }
}
