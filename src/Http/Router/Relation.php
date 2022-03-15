<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 21:17
 */

namespace ViaRest\Http\Router;


class Relation
{

    /**
     * @var string $relation
     * */
    public $relation;

    /**
     * Can item be created via this route
     *
     * @var bool
     * */
    public $canCreate = false;

    /**
     * Can relation be created via this route
     *
     * @var bool
     * */
    public $canAttach = false;

    /**
     * Can relation or item be updated via this route
     *
     * @var bool
     * */
    public $canUpdate = false;

    /**
     * Can relation or item be deleted via this route
     *
     * @var bool
     * */
    public $canDelete = false;

    /**
     * On delete, delete the entity or the relation
     *
     * @var bool
     * */
    public $softDelete = false;


    /**
     * Constructor
     *
     * @param string $relation
     * */
    public function __construct(string $relation)
    {
        $this->relation = $relation;
    }

    /**
     * @param bool $can
     * @return self
     * */
    public function canCreate($can = true): self
    {
        $this->canCreate = $can;
        return $this;
    }

    /**
     * @param bool $can
     * @return self
     * */
    public function canAttach($can = true): self
    {
        $this->canAttach = $can;
        return $this;
    }

    /**
     * @param bool $can
     * @return self
     * */
    public function canUpdate($can = true): self
    {
        $this->canUpdate = $can;
        return $this;
    }

    /**
     * @param bool $can
     * @return self
     * */
    public function canDelete($soft = true, $can = true): self
    {
        $this->softDelete = $soft;
        $this->canDelete = $can;
        return $this;
    }

}
