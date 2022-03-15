<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 21:17
 */

namespace ViaRest\Http\Router;


abstract class AbstractRoute implements RouteInterface
{

    const CREATE_ACTION = 'create';
    const READ_ALL_ACTION = 'read';
    const READ_ONE_ACTION = 'read';
    const UPDATE_ACTION = 'update';
    const DELETE_ACTION = 'delete';


    /**
     * @var string
     * */
    private $target;

    /**
     * @var array
     * */
    private $actions = [
        self::CREATE_ACTION,
        self::READ_ALL_ACTION,
        self::READ_ONE_ACTION,
        self::UPDATE_ACTION,
        self::DELETE_ACTION,
    ];

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
     * @return self
     */
    public function setTarget(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     * @return self
     */
    public function setActions(array $actions): self
    {
        $this->actions = $actions;
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
     * @return self
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
     * @return self
     */
    public function setPermission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

}
