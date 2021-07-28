<?php

namespace ViaRest\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RelationDetachedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * @var Relation
     * */
    protected $relation;

    /**
     * @var Model
     * */
    protected $root;

    /**
     * @var Model
     * */
    protected $target;


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
        $this->relation = $relation;
        $this->root = $root;
        $this->target = $target;
    }

}
