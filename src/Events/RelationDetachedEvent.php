<?php

namespace ViaRest\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Relations\Relation;

class RelationDetachedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * @var string
     * */
    public $relation;

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
     * @param string $relation
     * @param Model $root
     * @param Model $target
     * @return void
     */
    public function __construct(string $relation, Model $root, Model $target)
    {
        $this->relation = $relation;
        $this->root = $root;
        $this->target = $target;
    }

}
