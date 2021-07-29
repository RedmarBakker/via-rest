<?php

namespace ViaRest\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithPivotTable;

class RelationDetachedEvent
{
    use Dispatchable, InteractsWithSockets;


    /**
     * @var string
     * */
    public $table;

    /**
     * @var Model
     * */
    public $root;

    /**
     * @var Model
     * */
    public $target;


    /**
     * Create a new event instance.
     *
     * @param Relation $relation
     * @param Model $root
     * @param Model $target
     * @return void
     */
    public function __construct(Relation $relation, Model $root, Model $target)
    {
        $table = false;
        if (in_array(InteractsWithPivotTable::class, class_uses($relation))) {
            $table = $relation->getTable();
        }

        $this->table = $table;
        $this->root = $root;
        $this->target = $target;
    }

}
