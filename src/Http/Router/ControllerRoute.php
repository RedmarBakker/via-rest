<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 21:17
 */

namespace ViaRest\Http\Router;


class ControllerRoute implements RouteInterface
{

    /**
     * @var string
     * */
    private $target;

    /**
     * @var array
     * */
    private $relations;

    /**
     * @var array
     * */
    private $customs;


    /**
     * Constructor
     *
     * @param $target string
     * @param $relations array
     * @param $customs array
     * */
    public function __construct(string $target, array $relations = [], array $customs = [])
    {
        $this->target = $target;
        $this->relations = $relations;
        $this->customs = $customs;
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
    public function setTarget(string $target): void
    {
        $this->target = $target;
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
    public function setRelations(array $relations): void
    {
        $this->relations = $relations;
    }

    /**
     * @return array
     */
    public function getCustoms(): array
    {
        return $this->customs;
    }

    /**
     * @param array $customs
     */
    public function setCustoms(array $customs): void
    {
        $this->customs = $customs;
    }

}