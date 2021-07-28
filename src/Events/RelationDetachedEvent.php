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
    public $relationName;

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
     * @param string $relationName
     * @param Model $root
     * @param Model $target
     * @return void
     */
    public function __construct(string $relationName, Model $root, Model $target)
    {
        $this->relationName = $relationName;
        $this->root = $root;
        $this->target = $target;
    }

}
