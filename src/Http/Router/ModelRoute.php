<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 21:17
 */

namespace ViaRest\Http\Router;


class ModelRoute implements RouteInterface
{

    /**
     * @var string
     * */
    private $target;

    /**
     * @var array
     * */
    private $relations = [];

    /**
     * @var string
     * */
    private $permission = '';


    /**
     * Constructor
     *
     * @param $target string
     * @param $relations array
     * @param $customs array
     * */
    public function __construct(string $target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param array $relations
     */
    public function setRelations(array $relations): self
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * @return string
     */
    public function getPermission(): string
    {
        return $this->permission;
    }

    /**
     * @param string $permission
     */
    public function setPermission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

}
